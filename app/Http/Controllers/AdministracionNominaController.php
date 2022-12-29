<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App\http\Requests\GeneralRequest;
use App;
use Alert;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\Notificacion;
use Excel;
use DateTime;
use App\Functions\Files;
use Ilovepdf\CompressTask;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Illuminate\Support\Facades\Cookie;
use Lang;

class AdministracionNominaController extends Controller
{
	private $module_id =164;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data = App\Module::find($this->module_id);
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

	public function prenominaCreate()
	{
		if(Auth::user()->module->where('id',165)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			return view('administracion.nomina.alta-prenomina',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 165
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function prenominaEdit(App\Prenomina $prenomina)
	{
		if(Auth::user()->module->where('id',165)->count()>0)
		{
			if ($prenomina->status != 3) 
			{
				$data = App\Module::find($this->module_id);
				return view('administracion.nomina.alta-prenomina',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 165,
						'prenomina'	=> $prenomina
					]);
			}
			else
			{
				$alert	= "swal('', 'La prenómina ya escuentra eliminada', 'info');";
				return redirect()->route('nomina.index')->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function prenominaDelete(App\Prenomina $prenomina)
	{
		if(Auth::user()->module->where('id',165)->count()>0)
		{
			if ($prenomina->status == 2) 
			{
				$alert	= "swal('', 'La prenómina ya fue enviada anteriormente.', 'info');";
				return redirect()->route('nomina.index')->with('alert',$alert);
			}
			elseif ($prenomina->status == 3) 
			{
				$alert	= "swal('', 'La prenómina ya escuentra eliminada.', 'info');";
				return redirect()->route('nomina.index')->with('alert',$alert);
			}
			else
			{
				$prenomina->user_id	= Auth::user()->id;
				$prenomina->status	= 3;
				$prenomina->save();
				$alert				= "swal('', 'Prenómina eliminada exitosamente', 'success');";
				return redirect()->route('nomina.index')->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function getEmployee(Request $request)
	{
		if($request->ajax())
		{
			$output = "";
			$header = "";
			$footer = "";
			$option_id = isset($request->option_id) ? $request->option_id : "";

			if (isset($request->idrealEmployee) && $request->idrealEmployee != '') 
			{
				$employees = App\RealEmployee::where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.$request->search.'%')
					->whereNotIn('id',$request->idrealEmployee)
					->whereHas('workerDataVisible',function($query) use ($option_id)
					{
						$query->whereIn('workerStatus',[1,2,3,4,5]);
						if ($option_id != "") 
						{
							$query->whereIn('project',Auth::user()->inChargeProject(307)->pluck('project_id'));
						}
					})
					->paginate(10);
			}
			else
			{
				$employees = App\RealEmployee::where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.$request->search.'%')
					->whereHas('workerDataVisible',function($query) use ($option_id)
					{
						$query->whereIn('workerStatus',[1,2,3,4,5]);
						if ($option_id != "") 
						{
							$query->whereIn('project',Auth::user()->inChargeProject(307)->pluck('project_id'));
						}
					})
					->paginate(10);
			}
			if (count($employees)>0 && $request->search != '') 
			{

				$html		= '';
				$body		= [];
				$modelBody	= [];
				$modelHead	= [
					[
						["value" => "Nombre"],
						["value" => "Empresa"],
						["value" => "Proyecto"],
						["value" => "Acción"],
					]
				];

				foreach ($employees as $e) 
				{
					$fechaActual = date("Y-m-d H:i:s");
					if ($e->workerDataVisible->first()->downDate != '')
					{
						if (new \DateTime($e->workerDataVisible->first()->downDate) > new \DateTime($e->workerDataVisible->first()->imssDate)) 
						{
							if ($e->workerDataVisible->first()->regime_id == '09') 
							{
								$filter =
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"1\"",
									"classEx"		=> "filter"
								];
							}
							else
							{
								$datetime1	= date_create($e->workerDataVisible->first()->downDate);
								$datetime2	= date_create($fechaActual);
								$interval	= date_diff($datetime1, $datetime2);
								$difference = $interval->format('%a');
								if($difference <= 10)
								{
									$filter =
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" value=\"1\"",
										"classEx"		=> "filter"	
									];
								}
								else
								{
									$filter  =
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" value=\"0\"",
										"classEx"		=> "filter"	
									];
								}
							}
						}
						else
						{
							$filter =
							[
								"kind" 			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" value=\"1\"",
								"classEx"		=> "filter"
							];
						}
					}
					if ($e->workerDataVisible->first()->downDate == '')
					{
						if ($e->workerDataVisible->first()->imssDate == '') 
						{
							if ($e->workerDataVisible->first()->regime_id == '09') 
							{
								$filter = 
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"0\"",
									"classEx"		=> "filter"	
								];
							}
							else
							{
								$filter = 
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"1\"",
									"classEx"		=> "filter"
								];
							}
						}
						else
						{
							$filter = 
							[
								"kind" 			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" value=\"1\"",
								"classEx"		=> "filter"	
							];
						}
					}
					$enterprise = $e->workerDataVisible->first()->enterprises()->exists() ? $e->workerDataVisible->first()->enterprises->name 	: 'Sin Empresa';
					$project 	= $e->workerDataVisible->first()->projects()->exists() ? $e->workerDataVisible->first()->projects->proyectName 	: 'Sin Proyecto';

					$body = [ "classEx" => "tr_employee",
						[
							"content" => 
							[
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".$e->id."\"",
									"classEx"		=> "id-employee-table"
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".$e->workerDataVisible->first()->id."\"",
									"classEx"		=> "id-workerdata-table"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".$e->name." ".$e->last_name." ".$e->scnd_last_name."\"",
									"classEx"		=> "fullname-table"
								],
								[
									"label"			=> $e->name.' '.$e->last_name.' '.$e->scnd_last_name
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".$enterprise."\"",
									"classEx"		=> "enterprise-table"
								],
								[
									"label" => $enterprise
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".$project."\"",
									"classEx"		=> "project-table"
								],
								[
									"label" => $project
								]
							]
						],
						[
							"content" => 
							[
								"kind"			=> "components.buttons.button",
								"variant"		=> "warning",
								"label"			=> "<span class=\"icon-plus\"></span>",
								"classEx"   	=> "add-user",
								"attributeEx" 	=> "title=\"Agregar\" type=\"button\" value=\"".$e->id."\""
							]
						]
					];
					array_push($body[0]["content"], $filter);
					$modelBody[] = $body;
				}
				$html .= html_entity_decode(preg_replace("/(\r)*(\n)*/", "",view("components.tables.table", [
					"classEx" 			=> "my-4",
					"attributeExBody" 	=> "id=\"body-users\"",
					"classExBody"		=> "request-validate",
					"modelBody"			=> $modelBody,
					"modelHead"			=> $modelHead
				])));
				$html .= html_entity_decode(preg_replace("/(\r)*(\n)*/", "", "<div class='flex flex-row justify-center paginate'>".$employees->appends($_GET)->links()."</div>"));
				return Response(html_entity_decode($html));
			}
			else
			{
				$notfound = html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.not-found",[
					"attributeEx"	=> "role=alert",
					"slot"			=> "SIN RESULTADOS"
				])));
				return Response($notfound);
			}
		}
	}

	public function viewDetailEmployee(Request $request)
	{
		if ($request->ajax())
		{
			if($request->id!= '')
			{
				$employee = App\RealEmployee::find($request->id);
				return view('partials.employee_view_detail',['employee' => $employee]);
			}
		}
	}

	public function getDetailEmployee(Request $request)
	{
		if ($request->ajax())
		{
			if($request->id!= '')
			{
				$employee = App\RealEmployee::find($request->id);
				return view('partials.employee_edit_detail',['employee' => $employee]);
			}
		}
	}

	public function updateEmployee(Request $request)
	{
		if($request->ajax())
		{
			$t_realemployee                 = App\RealEmployee::find($request->idemployee);
			$t_realemployee->name           = $request->name;
			$t_realemployee->last_name      = $request->last_name;
			$t_realemployee->scnd_last_name = $request->scnd_last_name;
			$t_realemployee->curp           = $request->curp;
			$t_realemployee->rfc            = $request->rfc;
			$t_realemployee->imss           = $request->imss;
			$t_realemployee->street         = $request->street;
			$t_realemployee->number         = $request->number;
			$t_realemployee->colony         = $request->colony;
			$t_realemployee->cp             = $request->cp;
			$t_realemployee->city           = $request->city;
			$t_realemployee->state_id       = $request->state;
			$t_realemployee->email       	= $request->email;
			$t_realemployee->phone       	= $request->phone;
			$t_realemployee->save();
			$idworkingData = $request->idworkingData;

			if (isset($request->editworker) && $request->editworker != "") 
			{
				$old_workerdata          = App\WorkerData::find($request->idworkingData);
				$old_workerdata->visible = 0;
				$old_workerdata->save();
				$t_workerdata                    = new App\WorkerData();
				$t_workerdata->idEmployee        = $request->idemployee;
				$t_workerdata->state             = $request->work_state;
				$t_workerdata->project           = $request->work_project;
				$t_workerdata->enterprise        = $request->work_enterprise;
				$t_workerdata->account           = $request->work_account;
				$t_workerdata->direction         = $request->work_direction;
				$t_workerdata->department        = $request->work_department;
				$t_workerdata->position          = $request->work_position;
				$t_workerdata->immediate_boss    = $request->work_immediate_boss;
				$t_workerdata->admissionDate     = $request->work_income_date	!= '' ? Carbon::createFromFormat('d-m-Y',$request->work_income_date)->format('Y-m-d')	: null;
				$t_workerdata->imssDate          = $request->work_imss_date		!= '' ? Carbon::createFromFormat('d-m-Y',$request->work_imss_date)->format('Y-m-d') 	: null;
				$t_workerdata->downDate          = $request->work_down_date		!= '' ? Carbon::createFromFormat('d-m-Y',$request->work_down_date)->format('Y-m-d')		: null;
				$t_workerdata->endingDate        = $request->work_ending_date	!= '' ? Carbon::createFromFormat('d-m-Y',$request->work_ending_date)->format('Y-m-d')	: null;
				$t_workerdata->reentryDate       = $request->work_reentry_date	!= '' ? Carbon::createFromFormat('d-m-Y',$request->work_reentry_date)->format('Y-m-d')	: null;
				$t_workerdata->workerType        = $request->work_type_employee;
				$t_workerdata->regime_id         = $request->work_regime_employee;
				$t_workerdata->workerStatus      = $request->work_status_employee;
				$t_workerdata->status_imss       = $request->work_status_imss;
				$t_workerdata->sdi               = $request->work_sdi;
				$t_workerdata->periodicity       = $request->work_periodicity;
				$t_workerdata->netIncome         = $request->work_net_income;
				$t_workerdata->complement        = $request->work_complement;
				$t_workerdata->fonacot           = $request->work_fonacot;
				$t_workerdata->nomina            = $request->work_nomina;
				$t_workerdata->bono              = $request->work_bonus;
				$t_workerdata->employer_register = $request->work_employer_register;
				$t_workerdata->paymentWay        = $request->work_payment_way;
				$t_workerdata->admissionDateOld  = $request->work_income_date_old != '' ? Carbon::createFromFormat('d-m-Y',$request->work_income_date_old)->format('Y-m-d') : null;
				$t_workerdata->enterpriseOld     = $request->work_enterprise_old;
				$t_workerdata->recorder          = Auth::user()->id;
				if(isset($request->infonavit))
				{
					$t_workerdata->infonavitCredit			= $request->work_infonavit_credit;
					$t_workerdata->infonavitDiscount		= $request->work_infonavit_discount;
					$t_workerdata->infonavitDiscountType	= $request->work_infonavit_discount_type;
				}

				if(isset($request->alimony))
				{
					$t_workerdata->alimonyDiscount		= $request->work_alimony_discount;
					$t_workerdata->alimonyDiscountType	= $request->work_alimony_discount_type;
				}
				$t_workerdata->save();
				$idworkingData = $t_workerdata->id;
				if(isset($request->work_place) && count($request->work_place)>0)
				{
					$t_workerdata->places()->attach($request->work_place);
				}
				if(isset($request->work_wbs) && count($request->work_wbs)>0)
				{
					$t_workerdata->employeeHasWbs()->attach($request->work_wbs);
				}
				if(isset($request->work_subdepartment) && count($request->work_subdepartment)>0)
				{
					$t_workerdata->employeeHasSubdepartment()->attach($request->work_subdepartment);
				}
			}
			if (isset($request->deleteBank) && count($request->deleteBank)>0) 
			{
				for ($i=0; $i < count($request->deleteBank); $i++) 
				{ 
					if ($request->deleteBank[$i] != 'x') 
					{
						$delete          = App\EmployeeAccount::find($request->deleteBank[$i]);
						$delete->visible = 0;
						$delete->save();
					}
				} 
			}
			if(isset($request->idEmpAcc) && count($request->idEmpAcc)>0)
			{
				foreach ($request->idEmpAcc as $k => $e)
				{
					$empAcc              = new App\EmployeeAccount();
					$empAcc->idEmployee  = $request->idemployee;
					$empAcc->beneficiary = $request->beneficiary[$k];
					$empAcc->type        = $request->type_account[$k];
					$empAcc->alias       = $request->alias[$k];
					$empAcc->clabe       = $request->clabe[$k];
					$empAcc->account     = $request->account[$k];
					$empAcc->cardNumber  = $request->card[$k];
					$empAcc->branch      = $request->branch[$k];
					$empAcc->idCatBank   = $request->bank[$k];
					$empAcc->recorder    = Auth::user()->id;
					$empAcc->save();
				}
			}
			$idemployee = $request->idemployee;
			$fullname   = $request->name.' '.$request->last_name.' '.$request->scnd_last_name;
			$data       = [];
			$data[0]    = $idemployee;
			$data[1]    = $idworkingData;
			$data[2]    = $fullname;

			
			return Response($data);
		}
	}

	public function storePrenomina(Request $request)
	{
		if (Auth::user()->module->where('id',165)->count()>0) 
		{
			$typeOne		= []; // Obra - Fiscal 1 - 1
			$typeTwo		= []; // Obra - No fiscal 1 - 2
			$typeThree		= []; // Administrativa - Fiscal 2 - 1
			$typeFour		= []; // Administrativa - No fiscal 2 - 2
			$typeFive 		= []; // Obra - Nom35 1 - 3
			$typeSix 		= []; // Administrativa - Nom35 2 - 3
			
			$countTypeOne	= 0;
			$countTypeTwo	= 0;
			$countTypeThree	= 0;
			$countTypeFour	= 0;
			$countTypeFive 	= 0;
			$countTypeSix 	= 0;
			
			$prenomina_data = App\PrenominaEmployee::where('idprenomina',$request->prenom_id)->get();

			for ($i=0; $i < count($request->idrealEmployee); $i++) 
			{
				if ($request->type[$i] == 1 && ($request->fiscal[$i] == 1 || $request->fiscal[$i] == 3 || $request->fiscal[$i] == 5)) 
				{
					$data = $prenomina_data->where('idreal_employee',$request->idrealEmployee[$i]);
					$typeOne[$countTypeOne]['idemployee']	= $request->idrealEmployee[$i];
					$typeOne[$countTypeOne]['idworkerData']	= $request->idworkerData[$i];
					$typeOne[$countTypeOne]['absence']		= $data->first() != "" ? $data->first()->absence : '0';
					$typeOne[$countTypeOne]['extra_hours']	= $data->first() != "" ? $data->first()->extra_hours : '0';
					$typeOne[$countTypeOne]['holidays']		= $data->first() != "" ? $data->first()->holidays : '0';
					$typeOne[$countTypeOne]['sundays']		= $data->first() != "" ? $data->first()->sundays : '0';
					$countTypeOne++;
				}
				if ($request->type[$i] == 1 && ($request->fiscal[$i] == 2 || $request->fiscal[$i] == 3)) 
				{
					$data = $prenomina_data->where('idreal_employee',$request->idrealEmployee[$i]);
					$typeTwo[$countTypeTwo]['idemployee']	= $request->idrealEmployee[$i];
					$typeTwo[$countTypeTwo]['idworkerData']	= $request->idworkerData[$i];
					$typeTwo[$countTypeTwo]['absence']		= $data->first() != "" ? $data->first()->absence : '0';
					$typeTwo[$countTypeTwo]['extra_hours']	= $data->first() != "" ? $data->first()->extra_hours : '0';
					$typeTwo[$countTypeTwo]['holidays']		= $data->first() != "" ? $data->first()->holidays : '0';
					$typeTwo[$countTypeTwo]['sundays']		= $data->first() != "" ? $data->first()->sundays : '0';
					$countTypeTwo++;
				}
				if ($request->type[$i] == 2 && ($request->fiscal[$i] == 1 || $request->fiscal[$i] == 3 || $request->fiscal[$i] == 5)) 
				{
					$data = $prenomina_data->where('idreal_employee',$request->idrealEmployee[$i]);
					$typeThree[$countTypeThree]['idemployee']	= $request->idrealEmployee[$i];
					$typeThree[$countTypeThree]['idworkerData']	= $request->idworkerData[$i];
					$typeThree[$countTypeThree]['absence']		= $data->first() != "" ? $data->first()->absence : '0';
					$typeThree[$countTypeThree]['extra_hours']	= $data->first() != "" ? $data->first()->extra_hours : '0';
					$typeThree[$countTypeThree]['holidays']		= $data->first() != "" ? $data->first()->holidays : '0';
					$typeThree[$countTypeThree]['sundays']		= $data->first() != "" ? $data->first()->sundays : '0';
					$countTypeThree++;
				}
				if ($request->type[$i] == 2 && ($request->fiscal[$i] == 2 || $request->fiscal[$i] == 3)) 
				{
					$data = $prenomina_data->where('idreal_employee',$request->idrealEmployee[$i]);
					$typeFour[$countTypeFour]['idemployee']		= $request->idrealEmployee[$i];
					$typeFour[$countTypeFour]['idworkerData']	= $request->idworkerData[$i];
					$typeFour[$countTypeFour]['absence']		= $data->first() != "" ? $data->first()->absence : '0';
					$typeFour[$countTypeFour]['extra_hours']	= $data->first() != "" ? $data->first()->extra_hours : '0';
					$typeFour[$countTypeFour]['holidays']		= $data->first() != "" ? $data->first()->holidays : '0';
					$typeFour[$countTypeFour]['sundays']		= $data->first() != "" ? $data->first()->sundays : '0';
					$countTypeFour++;
				}
				if ($request->type[$i] == 1 && ($request->fiscal[$i] == 4 || $request->fiscal[$i] == 5)) 
				{
					$data = $prenomina_data->where('idreal_employee',$request->idrealEmployee[$i]);
					$typeFive[$countTypeFive]['idemployee']   	= $request->idrealEmployee[$i];
					$typeFive[$countTypeFive]['idworkerData'] 	= $request->idworkerData[$i];
					$typeFive[$countTypeFive]['absence']		= $data->first() != "" ? $data->first()->absence : '0';
					$typeFive[$countTypeFive]['extra_hours']	= $data->first() != "" ? $data->first()->extra_hours : '0';
					$typeFive[$countTypeFive]['holidays']		= $data->first() != "" ? $data->first()->holidays : '0';
					$typeFive[$countTypeFive]['sundays']		= $data->first() != "" ? $data->first()->sundays : '0';
					$countTypeFive++;
				}
				if ($request->type[$i] == 2 && ($request->fiscal[$i] == 4 || $request->fiscal[$i] == 5)) 
				{
					$data = $prenomina_data->where('idreal_employee',$request->idrealEmployee[$i]);
					$typeSix[$countTypeSix]['idemployee']   = $request->idrealEmployee[$i];
					$typeSix[$countTypeSix]['idworkerData'] = $request->idworkerData[$i];
					$typeSix[$countTypeSix]['absence']		= $data->first() != "" ? $data->first()->absence : '0';
					$typeSix[$countTypeSix]['extra_hours']	= $data->first() != "" ? $data->first()->extra_hours : '0';
					$typeSix[$countTypeSix]['holidays']		= $data->first() != "" ? $data->first()->holidays : '0';
					$typeSix[$countTypeSix]['sundays']		= $data->first() != "" ? $data->first()->sundays : '0';
					$countTypeSix++;
				}
			}
			if(isset($request->prenom_id))
			{
				$t_prenomina = App\Prenomina::find($request->prenom_id);
				if ($t_prenomina->status == 2 || $t_prenomina->status == 3) 
				{
					$alert	= "swal('', 'La prenómina ya escuentra eliminada', 'info');";
					return redirect()->route('nomina.index')->with('alert',$alert);
				}
				else
				{
					$t_prenomina->status = 2;
					$t_prenomina->save();
				}
			}
			else
			{
				$t_prenomina       = new App\Prenomina();
				$t_prenomina->date = Carbon::now();
				$t_prenomina->user_id = Auth::user()->id;
				$t_prenomina->save();
			}
			$idprenomina = $t_prenomina->idprenomina;
			if ($typeOne != null) 
			{
				$t_request               = new App\RequestModel();
				$t_request->kind         = 16;
				$t_request->fDate        = Carbon::now();
				$t_request->status       = 2;
				$t_request->idRequest    = $request->userid;
				$t_request->idElaborate  = Auth::user()->id;
				$t_request->idDepartment = 11;
				$t_request->taxPayment   = 1;
				$t_request->idprenomina  = $idprenomina;
				$t_request->save();
				$folio                      = $t_request->folio;
				$kind                       = $t_request->kind;
				$t_nomina                   = new App\Nomina();
				$t_nomina->title            = $request->title;
				$t_nomina->datetitle        = $request->datetitle != '' ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
				$t_nomina->idCatTypePayroll = $request->type_payroll;
				$t_nomina->idFolio          = $folio;
				$t_nomina->idKind           = $kind;
				$t_nomina->type_nomina      = 1;
				$t_nomina->save();
				$idnomina = $t_nomina->idnomina;
				foreach ($typeOne as $t) 
				{
					$t_nominaemployee					= new App\NominaEmployee();
					$t_nominaemployee->idrealEmployee	= $t['idemployee'];
					$t_nominaemployee->idworkingData	= $t['idworkerData'];
					$t_nominaemployee->type				= 1;
					$t_nominaemployee->fiscal			= 1;
					$t_nominaemployee->idnomina			= $idnomina;
					$t_nominaemployee->absence			= $t['absence'];
					$t_nominaemployee->extra_hours		= $t['extra_hours'];
					$t_nominaemployee->holidays			= $t['holidays'];
					$t_nominaemployee->sundays			= $t['sundays'];
					$t_nominaemployee->save();
				}
			}
			if ($typeTwo != null) 
			{
				$t_request               = new App\RequestModel();
				$t_request->kind         = 16;
				$t_request->fDate        = Carbon::now();
				$t_request->status       = 2;
				$t_request->idRequest    = $request->userid;
				$t_request->idElaborate  = Auth::user()->id;
				$t_request->idDepartment = 11;
				$t_request->taxPayment   = 0;
				$t_request->idprenomina  = $idprenomina;
				$t_request->save();
				$folio                      = $t_request->folio;
				$kind                       = $t_request->kind;
				$t_nomina                   = new App\Nomina();
				$t_nomina->title            = $request->title;
				$t_nomina->datetitle        = $request->datetitle != '' ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
				$t_nomina->idFolio          = $folio;
				$t_nomina->idKind           = $kind;
				$t_nomina->idCatTypePayroll = $request->type_payroll;
				$t_nomina->type_nomina      = 2;
				$t_nomina->save();
				$idnomina = $t_nomina->idnomina;
				foreach ($typeTwo as $t) 
				{
					$t_nominaemployee					= new App\NominaEmployee();
					$t_nominaemployee->idrealEmployee	= $t['idemployee'];
					$t_nominaemployee->idworkingData	= $t['idworkerData'];
					$t_nominaemployee->type				= 1;
					$t_nominaemployee->fiscal			= 2;
					$t_nominaemployee->idnomina			= $idnomina;
					$t_nominaemployee->absence			= $t['absence'];
					$t_nominaemployee->extra_hours		= $t['extra_hours'];
					$t_nominaemployee->holidays			= $t['holidays'];
					$t_nominaemployee->sundays			= $t['sundays'];
					$t_nominaemployee->save();
				}
			}
			if ($typeThree != null) 
			{
				$t_request               = new App\RequestModel();
				$t_request->kind         = 16;
				$t_request->fDate        = Carbon::now();
				$t_request->status       = 2;
				$t_request->idRequest    = $request->userid;
				$t_request->idElaborate  = Auth::user()->id;
				$t_request->idDepartment = 4;
				$t_request->taxPayment   = 1;
				$t_request->idprenomina  = $idprenomina;
				$t_request->save();
				$folio                      = $t_request->folio;
				$kind                       = $t_request->kind;
				$t_nomina                   = new App\Nomina();
				$t_nomina->title            = $request->title;
				$t_nomina->datetitle        = $request->datetitle != '' ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
				$t_nomina->idFolio          = $folio;
				$t_nomina->idKind           = $kind;
				$t_nomina->idCatTypePayroll = $request->type_payroll;
				$t_nomina->type_nomina      = 1;
				$t_nomina->save();
				$idnomina = $t_nomina->idnomina;
				foreach ($typeThree as $t) 
				{
					$t_nominaemployee					= new App\NominaEmployee();
					$t_nominaemployee->idrealEmployee	= $t['idemployee'];
					$t_nominaemployee->idworkingData	= $t['idworkerData'];
					$t_nominaemployee->type				= 2;
					$t_nominaemployee->fiscal			= 1;
					$t_nominaemployee->idnomina			= $idnomina;
					$t_nominaemployee->absence			= $t['absence'];
					$t_nominaemployee->extra_hours		= $t['extra_hours'];
					$t_nominaemployee->holidays			= $t['holidays'];
					$t_nominaemployee->sundays			= $t['sundays'];
					$t_nominaemployee->save();
				}
			}

			if ($typeFour != null) 
			{
				$t_request               = new App\RequestModel();
				$t_request->kind         = 16;
				$t_request->fDate        = Carbon::now();
				$t_request->status       = 2;
				$t_request->idRequest    = $request->userid;
				$t_request->idElaborate  = Auth::user()->id;
				$t_request->idDepartment = 4;
				$t_request->taxPayment   = 0;
				$t_request->idprenomina  = $idprenomina;
				$t_request->save();
				$folio                      = $t_request->folio;
				$kind                       = $t_request->kind;
				$t_nomina                   = new App\Nomina();
				$t_nomina->title            = $request->title;
				$t_nomina->datetitle        = $request->datetitle != '' ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
				$t_nomina->idFolio          = $folio;
				$t_nomina->idKind           = $kind;
				$t_nomina->idCatTypePayroll = $request->type_payroll;
				$t_nomina->type_nomina      = 2;
				$t_nomina->save();
				$idnomina = $t_nomina->idnomina;
				foreach ($typeFour as $t) 
				{
					$t_nominaemployee					= new App\NominaEmployee();
					$t_nominaemployee->idrealEmployee	= $t['idemployee'];
					$t_nominaemployee->idworkingData	= $t['idworkerData'];
					$t_nominaemployee->type				= 2;
					$t_nominaemployee->fiscal			= 2;
					$t_nominaemployee->idnomina			= $idnomina;
					$t_nominaemployee->absence			= $t['absence'];
					$t_nominaemployee->extra_hours		= $t['extra_hours'];
					$t_nominaemployee->holidays			= $t['holidays'];
					$t_nominaemployee->sundays			= $t['sundays'];
					$t_nominaemployee->save();
				}
			}

			if ($typeFive != null) 
			{
				$t_request               = new App\RequestModel();
				$t_request->kind         = 16;
				$t_request->fDate        = Carbon::now();
				$t_request->status       = 2;
				$t_request->idRequest    = $request->userid;
				$t_request->idElaborate  = Auth::user()->id;
				$t_request->idDepartment = 11;
				$t_request->taxPayment   = 0;
				$t_request->idprenomina  = $idprenomina;
				$t_request->save();
				$folio                      = $t_request->folio;
				$kind                       = $t_request->kind;
				$t_nomina                   = new App\Nomina();
				$t_nomina->title            = $request->title;
				$t_nomina->datetitle        = $request->datetitle != '' ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
				$t_nomina->idCatTypePayroll = $request->type_payroll;
				$t_nomina->idFolio          = $folio;
				$t_nomina->idKind           = $kind;
				$t_nomina->type_nomina      = 3;
				$t_nomina->save();
				$idnomina = $t_nomina->idnomina;
				foreach ($typeFive as $t) 
				{
					$t_nominaemployee					= new App\NominaEmployee();
					$t_nominaemployee->idrealEmployee	= $t['idemployee'];
					$t_nominaemployee->idworkingData	= $t['idworkerData'];
					$t_nominaemployee->type				= 1;
					$t_nominaemployee->fiscal			= 3;
					$t_nominaemployee->idnomina			= $idnomina;
					$t_nominaemployee->absence			= $t['absence'];
					$t_nominaemployee->extra_hours		= $t['extra_hours'];
					$t_nominaemployee->holidays			= $t['holidays'];
					$t_nominaemployee->sundays			= $t['sundays'];
					$t_nominaemployee->save();
				}
			}
			if ($typeSix != null) 
			{
				$t_request               = new App\RequestModel();
				$t_request->kind         = 16;
				$t_request->fDate        = Carbon::now();
				$t_request->status       = 2;
				$t_request->idRequest    = $request->userid;
				$t_request->idElaborate  = Auth::user()->id;
				$t_request->idDepartment = 4;
				$t_request->taxPayment   = 0;
				$t_request->idprenomina  = $idprenomina;
				$t_request->save();
				$folio                      = $t_request->folio;
				$kind                       = $t_request->kind;
				$t_nomina                   = new App\Nomina();
				$t_nomina->title            = $request->title;
				$t_nomina->datetitle        = $request->datetitle != '' ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
				$t_nomina->idFolio          = $folio;
				$t_nomina->idKind           = $kind;
				$t_nomina->idCatTypePayroll = $request->type_payroll;
				$t_nomina->type_nomina      = 3;
				$t_nomina->save();
				$idnomina = $t_nomina->idnomina;
				foreach ($typeSix as $t) 
				{
					$t_nominaemployee					= new App\NominaEmployee();
					$t_nominaemployee->idrealEmployee	= $t['idemployee'];
					$t_nominaemployee->idworkingData	= $t['idworkerData'];
					$t_nominaemployee->type				= 2;
					$t_nominaemployee->fiscal			= 3;
					$t_nominaemployee->idnomina			= $idnomina;
					$t_nominaemployee->absence			= $t['absence'];
					$t_nominaemployee->extra_hours		= $t['extra_hours'];
					$t_nominaemployee->holidays			= $t['holidays'];
					$t_nominaemployee->sundays			= $t['sundays'];
					$t_nominaemployee->save();
				}
			}
			$alert	= "swal('','".Lang::get("messages.request_sent")."', 'success');";
			return redirect('administration/nomina')->with('alert',$alert);
		}
	}

	public function selectMassive(Request $request)
	{
		$response = array(
			'error'		=> 'ERROR',
			'message'	=> 'Error, por favor intente nuevamente'
		);
		if ($request->ajax())
		{
			if($request->file('csv_file')->isValid())
			{
				$delimiters = ["," => 0, ";" => 0];
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
					$extention	= strtolower($request->file('csv_file')->getClientOriginalExtension());
					if($extention == 'csv')
					{
						$csvArr		= array();
						if (($handle = fopen($request->file('csv_file'), "r")) !== FALSE)
						{
							$first	= true;
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
						$response['table']	= '';

						$body		= [];
						$modelBody	= [];
						$modelHead	=[
							["value" => "", "show" => "true"],
							["value" => "Nombre del Empleado", "show" => "true"],
							["value" => "Tipo"],
							["value" => "Fiscal/No Fiscal/Nom35"],
							["value" => "Acciones"]
						];

						$selected			= 0;
						$total				= 0;
						$added				= array();
						foreach ($csvArr as $key => $e)
						{
							$exist	= App\RealEmployee::where('curp',trim($e['curp']))->get();
							if(count($exist)>0)
							{
								//$id = $request->id_enterprise;
								$employee			= $exist->first();
								//if ($employee->workerDataForEnterprise($id) != "" && !in_array($employee->id, $added)) 
								if(count($employee->workerDataVisible)>0 && ($employee->workerDataVisible->first()->workerStatus == 1 || $employee->workerDataVisible->first()->workerStatus == 2 || $employee->workerDataVisible->first()->workerStatus == 3)&& !in_array($employee->id, $added))
								{
									$added[]			= $employee->id;
									$response['error']	= 'DONE';
									$selectType	= "";
									$selectType	.= '<select class="border rounded py-2 px-3 m-px w-full" title="Tipo de nómina" name="type[]" data-validation="required">';
										$selectType .= '<option value="1" selected="selected">Obra</option>';
										$selectType .= '<option value="2">Administrativa</option>';
									$selectType .= '</select>';
						
									$fechaActual 	= date("Y-m-d H:i:s");
									$selectFiscal	= "";
									$selectFiscal	.= '<select class="border rounded py-2 px-3 m-px w-full" title="Fiscal/No Fiscal" name="fiscal[]" data-validation="required">';
										if ($employee->workerDataVisible->first()->downDate != '')
										{
											if (new \DateTime($employee->workerDataVisible->first()->downDate) > new \DateTime($employee->workerDataVisible->first()->imssDate)) 
											{
												if ($employee->workerDataVisible->first()->regime_id == '09') 
												{
													$selectFiscal .= '<option value="1" selected="selected">Fiscal</option>';
													$selectFiscal .= '<option value="2">No Fiscal</option>';
													$selectFiscal .= '<option value="3">Fiscal/No Fiscal</option>';
													$selectFiscal .= '<option value="4">Nom35</option>';
													$selectFiscal .= '<option value="5">Fiscal/Nom35</option>';
												}
												else
												{
													$datetime1	= date_create($employee->workerDataVisible->first()->downDate);
													$datetime2	= date_create($fechaActual);
													$interval	= date_diff($datetime1, $datetime2);
													$difference = $interval->format('%a');
													if($difference <= 10)
													{
														$selectFiscal .= '<option value="1" selected="selected">Fiscal</option>';
														$selectFiscal .= '<option value="2">No Fiscal</option>';
														$selectFiscal .= '<option value="3">Fiscal/No Fiscal</option>';
														$selectFiscal .= '<option value="4">Nom35</option>';
														$selectFiscal .= '<option value="5">Fiscal/Nom35</option>';
													}
													else
													{
														$selectFiscal .= '<option value="2" selected="selected">No Fiscal</option>';
													}
												}
											}
											else
											{
												$selectFiscal .= '<option value="1" selected="selected">Fiscal</option>';
												$selectFiscal .= '<option value="2">No Fiscal</option>';
												$selectFiscal .= '<option value="3">Fiscal/No Fiscal</option>';
												$selectFiscal .= '<option value="4">Nom35</option>';
												$selectFiscal .= '<option value="5">Fiscal/Nom35</option>';
											}
										}
										if ($employee->workerDataVisible->first()->downDate == '')
										{
											if ($employee->workerDataVisible->first()->imssDate == '') 
											{
												if ($employee->workerDataVisible->first()->regime_id == '09') 
												{
													$selectFiscal .= '<option value="1" selected="selected">Fiscal</option>';
													$selectFiscal .= '<option value="2">No Fiscal</option>';
													$selectFiscal .= '<option value="3">Fiscal/No Fiscal</option>';
													$selectFiscal .= '<option value="4">Nom35</option>';
													$selectFiscal .= '<option value="5">Fiscal/Nom35</option>';
												}
												else
												{
													$selectFiscal .= '<option value="2" selected="selected">No Fiscal</option>';
												}
											}
											else
											{
												$selectFiscal .= '<option value="1" selected="selected">Fiscal</option>';
												$selectFiscal .= '<option value="2">No Fiscal</option>';
												$selectFiscal .= '<option value="3">Fiscal/No Fiscal</option>';
												$selectFiscal .= '<option value="4">Nom35</option>';
												$selectFiscal .= '<option value="5">Fiscal/Nom35</option>';
											}
										}
									$selectFiscal .= '</select>';

									$body = [ "classEx" => "tr_payroll",
										[
											"show" => "true",
											"content" => 
											[
												"kind"			=> "components.inputs.checkbox",
												"attributeEx"	=> "id=\"type_check_$employee->id\" type=\"checkbox\" name=\"type_check[]\" value=\"".$employee->id."\"",
												"classEx"		=> "checkbox",
												"classExLabel"	=> "request-validate",
												"label"			=> "<span class=\"icon-check\"></span>"
											]
										],
										[
											"show" => "true",
											"content" => 
											[
												[
													"kind"			=> "components.inputs.input-text",
													"attributeEx"	=> "type=\"hidden\" name=\"idrealEmployee[]\" value=\"".$employee->id."\"",
													"classEx"		=> "idemployee-table-prenomina" 
												],
												[
													"kind"			=> "components.inputs.input-text",
													"attributeEx"	=> "type=\"hidden\" name=\"idworkerData[]\" value=\"".$employee->workerDataVisible->first()->id."\"",
													"classEx"		=> "idworkingdata-table-prenomina" 
												],
												[
													"kind"			=> "components.inputs.input-text",
													"attributeEx"	=> "type=\"hidden\" value=\"".$employee->name.' '.$employee->last_name.' '.$employee->scnd_last_name."\"",
													"classEx"		=> "fullname-table-prenomina" 
												],
												[
													"label"	=> $employee->name.' '.$employee->last_name.' '.$employee->scnd_last_name
												]
											]
										],
										[
											"content" => 
											[
												"label" => $selectType
											]
										],
										[
											"content" => 
											[
												"label" => $selectFiscal
											]
										],
										[
											"content" => 
											[
												[
													"kind" 			=> "components.buttons.button",
													"variant" 		=> "secondary",
													"attributeEx"	=> "title=\"Ver Datos\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\"",
													"classEx"		=> "btn-view-user",
													"label"			=> "<span class=\"icon-search\">"
												],
												[
													"kind" 			=> "components.buttons.button",
													"variant" 		=> "success",
													"attributeEx"	=> "title=\"Editar Datos\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\"",
													"classEx"		=> "btn-edit-user",
													"label"			=> "<span class=\"icon-pencil\">"
												],
												[
													"kind" 			=> "components.buttons.button",
													"variant" 		=> "red",
													"attributeEx"	=> "type=\"button\"",
													"classEx"		=> "btn-delete-tr",
													"label"			=> "<span class=\"icon-x\">"
												]
											]
										]
									];
									$modelBody[] = $body;
									$selected++;
									$response['curp'][] = $employee->curp;
								}
							}
							$total++;
						}
						$response['table'] .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table", [ 
							"modelBody"			=> $modelBody,
							"modelHead"			=> $modelHead,
							"noHead"			=> "true"
						])));

						if($selected == 0)
						{
							$response['message']	= 'Ningún empleado seleccionado, por favor asegúrese que los empleados contenidos en el archivo se encuentren dados de alta';
						}
						else
						{
							$response['message']	= 'Empleados seleccionados: '.$selected.' de un total de '.$total.' líneas en su CSV';
						}
					}
					else
					{
						$response['message']	= 'Archivo inválido, el archivo debe ser en formato CSV';
					}
				}
				else
				{
					$response['message'] = Lang::get("messages.separator_error");
				}
			}
			else
			{
				$response['message']	= 'Archivo inválido, por favor intente de nuevo';
			}
			return response($response);
		}
	}

	public function nominaSearch(Request $request)
	{
		if(Auth::user()->module->where('id',166)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$titleRequest = $request->titleRequest;
			$name         = $request->name;
			$folio        = $request->folio;
			$department   = $request->department;
			$mindate      = $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate      = $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$fiscal       = $request->fiscal;
			$type         = $request->type;
			$idEmployee   = $request->idEmployee;
			$requests = App\RequestModel::where('kind',16)
				->where('status',2)
				->where(function ($query) use ($name, $folio, $titleRequest, $fiscal, $department,$mindate,$maxdate,$type,$idEmployee)
				{
					if($name != "")
					{
						$query->whereHas('requestUser',function($q) use($name)
						{
							$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
						});
					}
					if($folio != "")
					{
						$query->where('folio',$folio);
					}
					if($titleRequest != "")
					{
						$query->whereHas('nomina',function($q) use($titleRequest)
						{
							$q->where('title','LIKE','%'.preg_replace("/\s+/", "%", $titleRequest).'%');
						});
					}
					if($idEmployee != "")
					{
						$query->whereHas('nomina',function($q) use($idEmployee)
						{
							$q->whereHas('nominaEmployee',function($q) use($idEmployee)
							{
								$q->whereIn('idrealEmployee',$idEmployee);
							});
						});
					}
					if ($fiscal != "") 
					{
						$query->whereHas('nominasReal',function($q) use ($fiscal)
						{
							$q->whereIn('type_nomina',$fiscal);
						});
					}
					if ($department) 
					{
						$query->where('idDepartment',$department);
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
					}
					if ($type != "") 
					{
						$query->whereHas('nominasReal',function($q) use ($type)
						{
							$q->where('idCatTypePayroll',$type);
						});
					}
				})
				->orderBy('fDate','DESC')
				->orderBy('folio','DESC')
				->paginate(10);
			return view('administracion.nomina.busqueda-nomina',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 166,
					'requests'		=> $requests,
					'name'			=> $name, 
					'mindate'		=> $request->mindate,
					'maxdate'		=> $request->maxdate,
					'folio'			=> $folio,
					'fiscal'		=> $fiscal,
					'titleRequest'	=> $titleRequest,
					'department'	=> $department,
					'type' 			=> $type,
					'idEmployee'	=> $idEmployee
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function nominaCreate($id)
	{
		if (Auth::user()->module->where('id',166)->count()>0) 
		{
			$data = App\Module::find($this->module_id);
			$request = App\RequestModel::find($id);

			if ($request != '') 
			{
				return view('administracion.nomina.alta-nomina',[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 166,
					'request'   => $request
				]);
			}
			else
			{
				return redirect('error');
			}
		}
	}

	public function nominaCreateNew($id)
	{
		if (Auth::user()->module->where('id',165)->count()>0) 
		{
			$data = App\Module::find($this->module_id);
			$request = App\RequestModel::find($id);

			if ($request != '') 
			{
				return view('administracion.nomina.nueva-nomina',[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 165,
					'request'	=>$request
				]);
			}
			else
			{
				return redirect('error');
			}
		}
	}

	public function getDetailEmployeeNomina(Request $request)
	{
		if ($request->ajax())
		{
			if($request->id!= '')
			{
				$folio			= $request->folio;
				$nominaemployee	= App\NominaEmployee::find($request->id);
				return view('administracion.nomina.modal.edit-employee',['nominaemployee' => $nominaemployee,'folio' => $folio]);
			}
		}
	}

	public function updateEmployeeNomina(Request $request)
	{		
		$t_realemployee					= App\RealEmployee::find($request->idemployee);
		$t_realemployee->name			= $request->name;
		$t_realemployee->last_name		= $request->last_name;
		$t_realemployee->scnd_last_name	= $request->scnd_last_name;
		$t_realemployee->curp			= $request->curp;
		$t_realemployee->rfc			= $request->rfc;
		$t_realemployee->imss			= $request->imss;
		$t_realemployee->street			= $request->street;
		$t_realemployee->number			= $request->number;
		$t_realemployee->colony			= $request->colony;
		$t_realemployee->cp				= $request->cp;
		$t_realemployee->city			= $request->city;
		$t_realemployee->state_id		= $request->state;
		$t_realemployee->email       	= $request->email;
		$t_realemployee->phone       	= $request->phone;
		$t_realemployee->save();
		$idworkingData					= $request->idworkingData;

		if (isset($request->editworker) && $request->editworker != "") 
		{
			$t_workerdata					= new App\WorkerData();
			$t_workerdata->idEmployee		= $request->idemployee;
			$t_workerdata->state			= $request->work_state;
			$t_workerdata->project			= $request->work_project;
			//$t_workerdata->wbs_id			= $request->work_wbs;
			$t_workerdata->enterprise		= $request->work_enterprise;
			$t_workerdata->account			= $request->work_account;
			$t_workerdata->direction		= $request->work_direction;
			$t_workerdata->department		= $request->work_department;
			$t_workerdata->position			= $request->work_position;
			$t_workerdata->immediate_boss	= $request->work_immediate_boss;
			$t_workerdata->admissionDate	= $request->work_income_date	!= "" ? Carbon::createFromFormat('d-m-Y',$request->work_income_date)->format('Y-m-d') 	: null;
			$t_workerdata->imssDate			= $request->work_imss_date		!= "" ? Carbon::createFromFormat('d-m-Y',$request->work_imss_date)->format('Y-m-d') 	: null;
			$t_workerdata->downDate			= $request->work_down_date		!= "" ? Carbon::createFromFormat('d-m-Y',$request->work_down_date)->format('Y-m-d') 	: null;
			$t_workerdata->endingDate		= $request->work_ending_date	!= "" ? Carbon::createFromFormat('d-m-Y',$request->work_ending_date)->format('Y-m-d') 	: null;
			$t_workerdata->reentryDate		= $request->work_reentry_date	!= "" ? Carbon::createFromFormat('d-m-Y',$request->work_reentry_date)->format('Y-m-d') 	: null;
			$t_workerdata->workerType		= $request->work_type_employee;
			$t_workerdata->regime_id 		= $request->work_regime_employee;
			$t_workerdata->workerStatus		= $request->work_status_employee;
			$t_workerdata->status_imss		= $request->work_status_imss;
			$t_workerdata->sdi				= $request->work_sdi;
			$t_workerdata->periodicity		= $request->work_periodicity;
			$t_workerdata->netIncome		= $request->work_net_income;
			$t_workerdata->complement		= $request->work_complement;
			$t_workerdata->fonacot			= $request->work_fonacot;
			$t_workerdata->nomina			= $request->work_nomina;
			$t_workerdata->bono				= $request->work_bonus;
			$t_workerdata->employer_register= $request->work_employer_register;
			$t_workerdata->paymentWay 		= $request->work_payment_way;
			$t_workerdata->admissionDateOld = $request->work_income_date_old != "" ? Carbon::createFromFormat('d-m-Y',$request->work_income_date_old)->format('Y-m-d') : null;
			$t_workerdata->enterpriseOld  	= $request->work_enterprise_old;
			$t_workerdata->visible			= 0;
			$t_workerdata->recorder			= Auth::user()->id;


			if(isset($request->infonavit))
			{
				$t_workerdata->infonavitCredit			= $request->work_infonavit_credit;
				$t_workerdata->infonavitDiscount		= $request->work_infonavit_discount;
				$t_workerdata->infonavitDiscountType	= $request->work_infonavit_discount_type;
			}
			if(isset($request->alimony))
			{
				$t_workerdata->alimonyDiscount		= $request->work_alimony_discount;
				$t_workerdata->alimonyDiscountType	= $request->work_alimony_discount_type;
			}

			$t_workerdata->save();

			$idworkingData = $t_workerdata->id;

			if(isset($request->work_place) && count($request->work_place)>0)
			{
				$t_workerdata->places()->attach($request->work_place);
			}

			if(isset($request->work_wbs) && count($request->work_wbs)>0)
			{
				$t_workerdata->employeeHasWbs()->attach($request->work_wbs);
			}

			if(isset($request->work_subdepartment) && count($request->work_subdepartment)>0)
			{
				$t_workerdata->employeeHasSubdepartment()->attach($request->work_subdepartment);
			}
		}

		if (isset($request->deleteBank) && count($request->deleteBank)>0) 
		{
			for ($i=0; $i < count($request->deleteBank); $i++) 
			{ 
				if ($request->deleteBank[$i] != 'x') 
				{
					$delete				= App\EmployeeAccount::find($request->deleteBank[$i]);
					$delete->visible	= 0;
					$delete->save();
				}
			} 
		}
		

		if(isset($request->idEmpAcc) && count($request->idEmpAcc)>0)
		{
			foreach ($request->idEmpAcc as $k => $e)
			{
				$empAcc					= new App\EmployeeAccount();
				$empAcc->idEmployee		= $request->idemployee;
				$empAcc->beneficiary	= $request->beneficiary[$k];
				$empAcc->type			= $request->type_account[$k];
				$empAcc->alias			= $request->alias[$k];
				$empAcc->clabe			= $request->clabe[$k];
				$empAcc->account		= $request->account[$k];
				$empAcc->cardNumber		= $request->card[$k];
				$empAcc->branch			= $request->branch[$k];
				$empAcc->idCatBank		= $request->bank[$k];
				$empAcc->recorder		= Auth::user()->id;
				$empAcc->save();
			}
		}

		$new_account	= $t_realemployee->bankData->where('visible',1)->where('type',1)->count()>0 ? $t_realemployee->bankData->where('visible',1)->where('type',1)->last()->id : null;

		$t_nominaemployee					= App\NominaEmployee::find($request->idnominaEmployee);
		$t_nominaemployee->idworkingData	= $idworkingData;
		if ($t_nominaemployee->nominasEmployeeNF()->exists()) 
		{
			$t_nominaemployee->nominasEmployeeNF->first()->idEmployeeAccounts = $new_account;
		}
		$t_nominaemployee->save();

		$folio	= $request->folio;
		$r = App\RequestModel::find($request->folio);

		$typePayroll = App\Nomina::find($t_nominaemployee->idnomina)->idCatTypePayroll;

		$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
		switch ($typePayroll) 
		{
			case '001':
				if ($r->status != 2 && $t_nominaemployee->salary()->exists()) 
				{
					$deleteAccounts = App\NominaEmployeeAccounts::where('idSalary',$t_nominaemployee->salary->first()->idSalary)->delete();

					$t_nominaemployeeaccount						= new App\NominaEmployeeAccounts();
					$t_nominaemployeeaccount->idSalary				= $t_nominaemployee->salary->first()->idSalary;
					$t_nominaemployeeaccount->idemployeeAccounts	= $new_account;
					$t_nominaemployeeaccount->save();

					if (!App\Http\Controllers\AdministracionNominaController::recalculateNomina($typePayroll,$request->idnominaEmployee)) 
					{
						$alert	= "swal('', '".Lang::get("messages.failed_to_recalculate")."', 'error');";
					}
				}
				break;
			
			case '002':
				if ($r->status != 2 && $t_nominaemployee->bonus()->exists()) 
				{
					if (!App\Http\Controllers\AdministracionNominaController::recalculateNomina($typePayroll,$request->idnominaEmployee)) 
					{
						$alert	= "swal('', '".Lang::get("messages.failed_to_recalculate")."', 'error');";
					}
				}
				break;

			case '003':
			case '004':
				if ($r->status != 2 && $t_nominaemployee->liquidation()->exists()) 
				{
					if (!App\Http\Controllers\AdministracionNominaController::recalculateNomina($typePayroll,$request->idnominaEmployee)) 
					{
						$alert	= "swal('', '".Lang::get("messages.failed_to_recalculate")."', 'error');";
					}
				}
				break;

			case '005':
				if ($r->status != 2 && $t_nominaemployee->vacationPremium()->exists()) 
				{
					if (!App\Http\Controllers\AdministracionNominaController::recalculateNomina($typePayroll,$request->idnominaEmployee)) 
					{
						$alert	= "swal('', '".Lang::get("messages.failed_to_recalculate")."', 'error');";
					}
				}
				break;

			case '006':
				if ($r->status != 2 && $t_nominaemployee->profitSharing()->exists()) 
				{
					if (!App\Http\Controllers\AdministracionNominaController::recalculateNomina($typePayroll,$request->idnominaEmployee)) 
					{
						$alert	= "swal('', '".Lang::get("messages.failed_to_recalculate")."', 'error');";
					}
				}
				break;

			default:
				break;
		}


		switch ($r->status) 
		{
			case 2:
				return redirect()->back()->with('alert',$alert);
				break;

			case 3:
				return redirect()->route('nomina.nomina-review',['id'=>$folio])->with('alert',$alert);
				break;

			case 15:
				return redirect()->route('nomina.nomina-authorization',['id'=>$folio])->with('alert',$alert);
				break;

			case 14:
				return redirect()->route('nomina.nomina-constructionreview',['id'=>$folio])->with('alert',$alert);
				break;

			case 4:
				return redirect()->route('nomina.nomina-authorization',['id'=>$folio])->with('alert',$alert);
				break;
			
			default:
				# code...
				break;
		}
	}

	public function getDataEmployeeNF(Request $request)
	{
		if ($request->ajax()) 
		{

			$folio				= $request->folio;
			$type				= App\RequestModel::find($folio)->taxPayment;
			$nominaemployee		= App\NominaEmployee::find($request->idnominaEmployee);
			$paymentWay			= $request->paymentWay;
			$idemployeeAccount	= $request->idemployeeAccount;

			if($request->id != '')
			{
				if ($type == 0) //PARA SOLICITUDES NO FISCALES
				{
					$employee = App\RealEmployee::find($request->id);
					return view('administracion.nomina.modal.datosnf',['employee' => $employee, 'nominaemployee' => $nominaemployee,'folio' => $folio,'paymentWay'=>$paymentWay,'idemployeeAccount'=>$idemployeeAccount]);
				}
			}
		}
	}

	public function updateDataEmployeeNF(Request $request)
	{

		if ($request->action == 'new') 
		{
			$t_nominaemployee = App\NominaEmployee::find($request->idnominaEmployee);

			$t_nominaemployeenf						= new App\NominaEmployeeNF();
			$t_nominaemployeenf->idnominaEmployee	= $request->idnominaEmployee;

			if ($request->method_request == 1) 
			{
				if ($request->idEmployeeAccounts == '' && $t_nominaemployee->employee->first()->bankData()->where('visible',1)->exists()) 
				{
					$t_nominaemployeenf->idpaymentMethod	= $request->method_request;
					$t_nominaemployeenf->idemployeeAccounts	= $t_nominaemployee->employee->first()->bankData->where('visible',1)->first()->id;
				}
				elseif ($request->idEmployeeAccounts != '')
				{
					$t_nominaemployeenf->idpaymentMethod	= $request->method_request;
					$t_nominaemployeenf->idemployeeAccounts	= $request->idEmployeeAccounts;
				}
				else 
				{
					$t_nominaemployeenf->idpaymentMethod	= 2;
					$t_nominaemployeenf->idemployeeAccounts	= null;
				}
			}
			else
			{
				$t_nominaemployeenf->idpaymentMethod	= $request->method_request;
				$t_nominaemployeenf->idemployeeAccounts	= null;
			}

			$t_nominaemployeenf->reference			= $request->employee_reference;
			$t_nominaemployeenf->discount			= $request->employee_discount;
			$t_nominaemployeenf->reasonDiscount		= $request->employee_reason_discount;
			$t_nominaemployeenf->complementPartial	= $request->employee_complement;
			$t_nominaemployeenf->amount				= $request->employee_amount;
			$t_nominaemployeenf->reasonAmount		= $request->employee_reason_payment;
			$t_nominaemployeenf->extra_time			= $request->employee_extra_time;
			$t_nominaemployeenf->holiday			= $request->employee_holiday;
			$t_nominaemployeenf->sundays			= $request->employee_sundays;
			$t_nominaemployeenf->save();

			if (isset($request->t_employee_extra) && count($request->t_employee_extra)>0) 
			{
				for ($i=0; $i < count($request->t_employee_extra); $i++) 
				{ 
					$t_extra						= new App\ExtrasNomina();
					$t_extra->amount				= $request->t_employee_extra[$i];
					$t_extra->reason				= $request->t_employee_reason_extra[$i];
					$t_extra->idnominaemployeenf	= $t_nominaemployeenf->idnominaemployeenf;
					$t_extra->save();
				}
			}

			if (isset($request->t_employee_discount) && count($request->t_employee_discount)>0) 
			{
				for ($i=0; $i < count($request->t_employee_discount); $i++) 
				{ 
					$t_discount						= new App\DiscountsNomina();
					$t_discount->amount				= $request->t_employee_discount[$i];
					$t_discount->reason				= $request->t_employee_reason_discount[$i];
					$t_discount->idnominaemployeenf	= $t_nominaemployeenf->idnominaemployeenf;
					$t_discount->save();
				}
			}

			$folio  = $request->folio;
			$totalRequest		= 0;
			
			$t_nominaemployee	= App\NominaEmployee::find($t_nominaemployeenf->idnominaEmployee);
			$t_nomina			= App\Nomina::find($t_nominaemployee->idnomina);

			foreach ($t_nomina->nominaEmployee as $n) 
			{
				$totalRequest += $n->nominasEmployeeNF()->exists() ? $n->nominasEmployeeNF->first()->amount : 0;
			}

			$t_nomina->amount	= $totalRequest;
			$t_nomina->save();
			
			$alert	= "swal('','".Lang::get("messages.record_created")."', 'success');";
			
			$r = App\RequestModel::find($request->folio);

			switch ($r->status) 
			{
				case 2:
					return redirect()->route('nomina.nomina-create',['id'=>$folio])->with('alert',$alert);
					break;

				case 3:
					return redirect()->route('nomina.nomina-review',['id'=>$folio])->with('alert',$alert);
					break;

				case 15:
					return redirect()->route('nomina.nomina-authorization',['id'=>$folio])->with('alert',$alert);
					break;

				case 14:
					return redirect()->route('nomina.nomina-constructionreview',['id'=>$folio])->with('alert',$alert);
					break;

				case 4:
					return redirect()->route('nomina.nomina-authorization',['id'=>$folio])->with('alert',$alert);
					break;
				
				default:
					# code...
					break;
			}
		}
		else
		{
			$t_nominaemployee 						= App\NominaEmployee::find($request->idnominaEmployee);

			$t_nominaemployeenf						= App\NominaEmployeeNF::find($request->idnominaemployeenf);
			$t_nominaemployeenf->idnominaEmployee	= $request->idnominaEmployee;

			if ($request->method_request == 1) 
			{
				if ($request->idEmployeeAccounts == '' && $t_nominaemployee->employee->first()->bankData()->where('visible',1)->exists()) 
				{
					$t_nominaemployeenf->idpaymentMethod	= $request->method_request;
					$t_nominaemployeenf->idemployeeAccounts	= $t_nominaemployee->employee->first()->bankData->where('visible',1)->first()->id;
				}
				elseif ($request->idEmployeeAccounts != '')
				{
					$t_nominaemployeenf->idpaymentMethod	= $request->method_request;
					$t_nominaemployeenf->idemployeeAccounts	= $request->idEmployeeAccounts;
				}
				else 
				{
					$t_nominaemployeenf->idpaymentMethod	= 2;
					$t_nominaemployeenf->idemployeeAccounts	= null;
				}

			}
			else
			{
				$t_nominaemployeenf->idpaymentMethod	= $request->method_request;
				$t_nominaemployeenf->idemployeeAccounts	= null;
			}

			$t_nominaemployeenf->reference			= $request->employee_reference;
			$t_nominaemployeenf->discount			= $request->employee_discount;
			$t_nominaemployeenf->reasonDiscount		= $request->employee_reason_discount;
			$t_nominaemployeenf->complementPartial	= $request->employee_complement;
			$t_nominaemployeenf->amount				= $request->employee_amount;
			$t_nominaemployeenf->reasonAmount		= $request->employee_reason_payment;
			$t_nominaemployeenf->extra_time			= $request->employee_extra_time;
			$t_nominaemployeenf->holiday			= $request->employee_holiday;
			$t_nominaemployeenf->sundays			= $request->employee_sundays;
			$t_nominaemployeenf->save();

			if (isset($request->delete_discount) && count($request->delete_discount)) 
			{
				App\DiscountsNomina::whereIn('id',$request->delete_discount)->delete();
			}

			if (isset($request->delete_extra) && count($request->delete_extra)) 
			{
				App\ExtrasNomina::whereIn('id',$request->delete_extra)->delete();
			}

			if (isset($request->t_employee_extra) && count($request->t_employee_extra)>0) 
			{
				for ($i=0; $i < count($request->t_employee_extra); $i++) 
				{ 
					if ($request->t_id_extra[$i] == 'x') 
					{
						$t_extra						= new App\ExtrasNomina();
						$t_extra->amount				= $request->t_employee_extra[$i];
						$t_extra->reason				= $request->t_employee_reason_extra[$i];
						$t_extra->idnominaemployeenf	= $t_nominaemployeenf->idnominaemployeenf;
						$t_extra->save();
					}
				}
			}

			if (isset($request->t_employee_discount) && count($request->t_employee_discount)>0) 
			{
				for ($i=0; $i < count($request->t_employee_discount); $i++) 
				{ 
					if ($request->t_id_discount[$i] == 'x') 
					{
						$t_discount						= new App\DiscountsNomina();
						$t_discount->amount				= $request->t_employee_discount[$i];
						$t_discount->reason				= $request->t_employee_reason_discount[$i];
						$t_discount->idnominaemployeenf	= $t_nominaemployeenf->idnominaemployeenf;
						$t_discount->save();
					}
				}
			}

			$folio				= $request->folio;
			$totalRequest		= 0;
			
			$t_nominaemployee	= App\NominaEmployee::find($t_nominaemployeenf->idnominaEmployee);
			$t_nomina			= App\Nomina::find($t_nominaemployee->idnomina);

			foreach ($t_nomina->nominaEmployee as $n) 
			{
				$totalRequest += $n->nominasEmployeeNF()->exists() ? $n->nominasEmployeeNF->first()->amount : 0;
			}

			$t_nomina->amount	= $totalRequest;
			$t_nomina->save();
			$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";

			$r = App\RequestModel::find($request->folio);

			switch ($r->status) 
			{
				case 2:
					return redirect()->route('nomina.nomina-create',['id'=>$folio])->with('alert',$alert);
					break;

				case 3:
					return redirect()->route('nomina.nomina-review',['id'=>$folio])->with('alert',$alert);
					break;

				case 15:
					return redirect()->route('nomina.nomina-authorization',['id'=>$folio])->with('alert',$alert);
					break;

				case 14:
					return redirect()->route('nomina.nomina-constructionreview',['id'=>$folio])->with('alert',$alert);
					break;

				case 4:
					return redirect()->route('nomina.nomina-authorization',['id'=>$folio])->with('alert',$alert);
					break;
			}
		}
	}

	public function updateDataEmployeeF(Request $request,$id)
	{
		switch ($request->idtypepayroll) 
		{
			case '001':
				$t_salary                          = App\Salary::find($id);
				$t_salary->sd                      = $request->salary_sd;
				$t_salary->sdi                     = $request->salary_sdi;
				$t_salary->workedDays              = $request->salary_workedDays;
				$t_salary->daysForImss             = $request->salary_daysForImss;
				$t_salary->salary                  = $request->salary_salary;
				$t_salary->loan_perception         = $request->salary_loan_perception;
				$t_salary->puntuality              = $request->salary_puntuality;
				$t_salary->assistance              = $request->salary_assistance;
				$t_salary->extra_time_taxed        = $request->salary_extra_hours_taxed;
				$t_salary->extra_time              = $request->salary_extra_hours_taxed + $request->salary_extra_hours;
				$t_salary->holiday_taxed           = $request->salary_holiday_taxed;
				$t_salary->holiday                 = $request->salary_holiday_taxed + $request->salary_holiday;
				$t_salary->exempt_sunday           = $request->salary_except_sundays;
				$t_salary->taxed_sunday            = $request->salary_taxed_sundays;
				$t_salary->subsidy                 = $request->salary_subsidy;
				$t_salary->totalPerceptions        = $request->salary_totalPerceptions;
				$t_salary->imss                    = $request->salary_imss;
				$t_salary->infonavit               = $request->salary_infonavit;
				$t_salary->fonacot                 = $request->salary_fonacot;
				$t_salary->loan_retention          = $request->salary_loan_retention;
				$t_salary->other_retention_amount  = $request->salary_other_retention_amount;
				$t_salary->other_retention_concept = $request->salary_other_retention_concept;
				$t_salary->isrRetentions           = $request->salary_isrRetentions;
				$t_salary->totalRetentions         = $request->salary_totalRetentions;
				$t_salary->netIncome               = $request->salary_netIncome;
				$t_salary->alimony                 = $request->salary_alimony;
				$t_salary->idAccountBeneficiary    = $request->salary_idAccountBeneficiary;
				$deleteAccounts                    = App\NominaEmployeeAccounts::where('idSalary',$id)->delete();
				$t_nominaemployee                  = App\NominaEmployee::find($t_salary->idnominaEmployee);
				if($request->salary_idpaymentMethod == 1) 
				{
					if ($request->salary_idemployeeAccounts == '' && $t_nominaemployee->employee->first()->bankData()->where('visible',1)->exists()) 
					{
						$t_salary->idpaymentMethod                   = $request->salary_idpaymentMethod; 
						$t_nominaemployeeaccount                     = new App\NominaEmployeeAccounts();
						$t_nominaemployeeaccount->idSalary           = $t_salary->idSalary;
						$t_nominaemployeeaccount->idemployeeAccounts = $t_nominaemployee->employee->first()->bankData->where('visible',1)->first()->id;
						$t_nominaemployeeaccount->save();
					}
					elseif ($request->salary_idemployeeAccounts != '') 
					{
						$t_salary->idpaymentMethod = $request->salary_idpaymentMethod; 
						for($i = 0; $i < count($request->salary_idemployeeAccounts); $i++) 
						{
							$t_nominaemployeeaccount                     = new App\NominaEmployeeAccounts();
							$t_nominaemployeeaccount->idSalary           = $t_salary->idSalary;
							$t_nominaemployeeaccount->idemployeeAccounts = $request->salary_idemployeeAccounts[$i];
							$t_nominaemployeeaccount->save();
						}
					}
					else
					{
						$t_salary->idpaymentMethod = 2; 
					}
				}
				else
				{
					$t_salary->idpaymentMethod = $request->salary_idpaymentMethod; 
				}
				$t_salary->save();
				$t_nominaemployee = App\NominaEmployee::find($t_salary->idnominaEmployee);
				$totalRequest     = 0;
				$t_nomina         = App\Nomina::find($t_nominaemployee->idnomina);
				foreach($t_nomina->nominaEmployee as $n) 
				{
					$totalRequest += $n->salary->first()->netIncome;
				}

				$t_nomina->amount = $totalRequest;
				$t_nomina->save();


				$req			= App\RequestModel::find($t_nomina->idFolio);
				$nom_no_fiscal 	= App\RequestModel::where('kind',16)
								->where('idprenomina',$req->idprenomina)
								->where('idDepartment',$req->idDepartment)
								->where('taxPayment',0)
								->get();
				if($nom_no_fiscal != '')
				{
					foreach ($nom_no_fiscal as $request_nf) 
					{
						$nom_emp_nf 			= App\NominaEmployee::where('idrealEmployee',$t_nominaemployee->idrealEmployee)
													->where('idnomina',$request_nf->nominasReal->first()->idnomina)
													->first();
						$idnominaemployeenf 	= $nom_emp_nf  != "" && $nom_emp_nf->nominasEmployeeNF()->exists() ? $nom_emp_nf->nominasEmployeeNF->first()->idnominaemployeenf : "";
						if ($idnominaemployeenf != "") 
						{
							$nomina_nf 				= App\NominaEmployeeNF::find($idnominaemployeenf);
							$nomina_nf->netIncome 	= $nomina_nf->amount + $t_salary->netIncome;
							$nomina_nf->save();
						}
					}
				}	
				break;

			case '002':
				$t_bonus                                    = App\Bonus::find($id);
				$t_bonus->sd                                = $request->bonus_sd; 
				$t_bonus->sdi                               = $request->bonus_sdi;
				$t_bonus->dateOfAdmission                   = $request->bonus_dateOfAdmission != "" ? Carbon::createFromFormat('d-m-Y',$request->bonus_dateOfAdmission)->format('Y-m-d') : null;
				$t_bonus->daysForBonuses                    = $request->bonus_daysForBonuses; 
				$t_bonus->proportionalPartForChristmasBonus = $request->bonus_proportionalPartForChristmasBonus; 
				$t_bonus->exemptBonus                       = $request->bonus_exemptBonus; 
				$t_bonus->taxableBonus                      = $request->bonus_taxableBonus; 
				$t_bonus->totalPerceptions                  = $request->bonus_totalPerceptions; 
				$t_bonus->isr                               = $request->bonus_isr; 
				$t_bonus->totalTaxes                        = $request->bonus_totalTaxes; 
				$t_bonus->netIncome                         = $request->bonus_netIncome;  
				$t_bonus->alimony                           = $request->bonus_alimony;
				$t_bonus->idAccountBeneficiary              = $request->bonus_idAccountBeneficiary; 
				$deleteAccounts                             = App\NominaEmployeeAccounts::where('idBonus',$id)->delete();
				$t_nominaemployee                           = App\NominaEmployee::find($t_bonus->idnominaEmployee);
				if ($request->bonus_idpaymentMethod == 1) 
				{
					if ($request->bonus_idemployeeAccounts == '' && $t_nominaemployee->employee->first()->bankData()->where('visible',1)->exists()) 
					{
						$t_bonus->idpaymentMethod                    = $request->bonus_idpaymentMethod;
						$t_nominaemployeeaccount                     = new App\NominaEmployeeAccounts();
						$t_nominaemployeeaccount->idBonus            = $t_bonus->idBonus;
						$t_nominaemployeeaccount->idemployeeAccounts = $t_nominaemployee->employee->first()->bankData->where('visible',1)->first()->id;
						$t_nominaemployeeaccount->save();
					}
					elseif ($request->bonus_idemployeeAccounts != '') 
					{
						$t_bonus->idpaymentMethod = $request->bonus_idpaymentMethod;
						for ($i=0; $i < count($request->bonus_idemployeeAccounts); $i++) 
						{
							$t_nominaemployeeaccount                     = new App\NominaEmployeeAccounts();
							$t_nominaemployeeaccount->idBonus            = $t_bonus->idBonus;
							$t_nominaemployeeaccount->idemployeeAccounts = $request->bonus_idemployeeAccounts[$i];
							$t_nominaemployeeaccount->save();
						}
					}
					else
					{
						$t_bonus->idpaymentMethod = 2;
					}
				}
				else
				{
					$t_bonus->idpaymentMethod = $request->bonus_idpaymentMethod;
				}
				$t_bonus->save();
				$t_nominaemployee            = App\NominaEmployee::find($t_bonus->idnominaEmployee);
				$t_nominaemployee->day_bonus = $request->bonus_daysForBonuses;
				$t_nominaemployee->save();
				$totalRequest = 0;
				$t_nomina     = App\Nomina::find($t_nominaemployee->idnomina);
				foreach($t_nomina->nominaEmployee as $n)
				{
					$totalRequest += $n->bonus->first()->netIncome;
				}
				$t_nomina->amount = $totalRequest;
				$t_nomina->save();
				break;
			case '003':
			case '004':
				$t_liquidation                = App\Liquidation::find($id);
				$t_liquidation->sd            = $request->liquidation_sd;
				$t_liquidation->sdi           = $request->liquidation_sdi;
				$t_liquidation->admissionDate = $request->liquidation_admissionDate != "" ? Carbon::createFromFormat('d-m-Y',$request->liquidation_admissionDate)->format('Y-m-d') 	: null;
				$t_liquidation->downDate      = $request->liquidation_downDate 		!= "" ? Carbon::createFromFormat('d-m-Y',$request->liquidation_downDate)->format('Y-m-d') 		: null;
				$t_liquidation->fullYears     = $request->liquidation_fullYears;
				$t_liquidation->workedDays    = $request->liquidation_workedDays;
				$t_liquidation->holidayDays   = $request->liquidation_holidayDays;
				$t_liquidation->bonusDays     = $request->liquidation_bonusDays;
				if ($request->idtypepayroll=='004') 
				{
					$t_liquidation->liquidationSalary			= $request->liquidation_liquidationSalary;
					$t_liquidation->twentyDaysPerYearOfServices	= $request->liquidation_twentyDaysPerYearOfServices;
				}
				$t_liquidation->seniorityPremium     = $request->liquidation_seniorityPremium;
				$t_liquidation->exemptCompensation   = $request->liquidation_exemptCompensation;
				$t_liquidation->taxedCompensation    = $request->liquidation_taxedCompensation;
				$t_liquidation->holidays             = $request->liquidation_holidays;
				$t_liquidation->exemptBonus          = $request->liquidation_exemptBonus;
				$t_liquidation->taxableBonus         = $request->liquidation_taxableBonus;
				$t_liquidation->holidayPremiumExempt = $request->liquidation_holidayPremiumExempt;
				$t_liquidation->holidayPremiumTaxed  = $request->liquidation_holidayPremiumTaxed;
				$t_liquidation->otherPerception      = $request->liquidation_otherPerception;
				$t_liquidation->totalPerceptions     = $request->liquidation_totalPerceptions;
				$t_liquidation->isr                  = $request->liquidation_isr;
				$t_liquidation->other_retention      = $request->liquidation_otherRetention;
				$t_liquidation->totalRetentions      = $request->liquidation_totalRetentions;
				$t_liquidation->netIncome            = $request->liquidation_netIncome;
				$t_liquidation->alimony              = $request->liquidation_alimony;
				$t_liquidation->idAccountBeneficiary = $request->liquidation_idAccountBeneficiary; 
				$deleteAccounts                      = App\NominaEmployeeAccounts::where('idLiquidation',$id)->delete();
				$t_nominaemployee                    = App\NominaEmployee::find($t_liquidation->idnominaEmployee);
				if ($request->liquidation_idpaymentMethod == 1) 
				{
					if ($request->liquidation_idEmployeeAccounts == '' && $t_nominaemployee->employee->first()->bankData()->where('visible',1)->exists())
					{
						$t_liquidation->idpaymentMethod              = $request->liquidation_idpaymentMethod;
						$t_nominaemployeeaccount                     = new App\NominaEmployeeAccounts();
						$t_nominaemployeeaccount->idLiquidation      = $t_liquidation->idLiquidation;
						$t_nominaemployeeaccount->idemployeeAccounts = $t_nominaemployee->employee->first()->bankData->where('visible',1)->first()->id;
						$t_nominaemployeeaccount->save();
					}
					elseif ($request->liquidation_idEmployeeAccounts != '') 
					{
						$t_liquidation->idpaymentMethod = $request->liquidation_idpaymentMethod;
						for ($i=0; $i < count($request->liquidation_idEmployeeAccounts); $i++) 
						{
							$t_nominaemployeeaccount                     = new App\NominaEmployeeAccounts();
							$t_nominaemployeeaccount->idLiquidation      = $t_liquidation->idLiquidation;
							$t_nominaemployeeaccount->idemployeeAccounts = $request->liquidation_idEmployeeAccounts[$i];
							$t_nominaemployeeaccount->save();
						}
					}
					else
					{
						$t_liquidation->idpaymentMethod = 2;
					}
				}
				else
				{
					$t_liquidation->idpaymentMethod = $request->liquidation_idpaymentMethod;
				}
				$t_liquidation->save();
				$t_nominaemployee                   = App\NominaEmployee::find($t_liquidation->idnominaEmployee);
				$t_nominaemployee->worked_days      = $request->liquidation_workedDays;
				$t_nominaemployee->down_date        = $request->liquidation_downDate != "" ? Carbon::createFromFormat('d-m-Y',$request->liquidation_downDate)->format('Y-m-d') : null;
				$t_nominaemployee->other_perception = $request->liquidation_otherPerception;
				$t_nominaemployee->other_retention 	= $request->liquidation_otherRetention;
				$t_nominaemployee->save();
				$totalRequest = 0;
				$t_nomina     = App\Nomina::find($t_nominaemployee->idnomina);
				foreach($t_nomina->nominaEmployee as $n)
				{
					$totalRequest += $n->liquidation->first()->netIncome;
				}
				$t_nomina->amount = $totalRequest;
				$t_nomina->save();
				break;
			case '005':
				$t_vacationpremium                       = App\VacationPremium::find($id);
				$t_vacationpremium->sd                   = $request->vacationpremium_sd;
				$t_vacationpremium->sdi                  = $request->vacationpremium_sdi;
				$t_vacationpremium->workedDays           = $request->vacationpremium_workedDays;
				$t_vacationpremium->holidaysDays         = $request->vacationpremium_holidaysDays;
				$t_vacationpremium->holidays             = $request->vacationpremium_holidays;
				$t_vacationpremium->exemptHolidayPremium = $request->vacationpremium_exemptHolidayPremium;
				$t_vacationpremium->holidayPremiumTaxed  = $request->vacationpremium_holidayPremiumTaxed;
				$t_vacationpremium->totalPerceptions     = $request->vacationpremium_totalPerceptions;
				$t_vacationpremium->isr                  = $request->vacationpremium_isr;
				$t_vacationpremium->totalTaxes           = $request->vacationpremium_totalTaxes;
				$t_vacationpremium->netIncome            = $request->vacationpremium_netIncome;
				$t_vacationpremium->alimony              = $request->vacationpremium_alimony;
				$t_vacationpremium->idAccountBeneficiary = $request->vacationpremium_idAccountBeneficiary; 
				$deleteAccounts                          = App\NominaEmployeeAccounts::where('idvacationPremium',$id)->delete();
				$t_nominaemployee                        = App\NominaEmployee::find($t_vacationpremium->idnominaEmployee);
				if ($request->vacationpremium_idpaymentMethod == 1) 
				{
					if ($request->vacationpremium_idemployeeAccounts == '' && $t_nominaemployee->employee->first()->bankData()->where('visible',1)->exists()) 
					{
						$t_vacationpremium->idpaymentMethod          = $request->vacationpremium_idpaymentMethod;
						$t_nominaemployeeaccount                     = new App\NominaEmployeeAccounts();
						$t_nominaemployeeaccount->idvacationPremium  = $t_vacationpremium->idvacationPremium;
						$t_nominaemployeeaccount->idemployeeAccounts = $t_nominaemployee->employee->first()->bankData->where('visible',1)->first()->id;
						$t_nominaemployeeaccount->save();
					}
					elseif ($request->vacationpremium_idemployeeAccounts != '') 
					{
						$t_vacationpremium->idpaymentMethod = $request->vacationpremium_idpaymentMethod;
						for ($i=0; $i < count($request->vacationpremium_idemployeeAccounts); $i++) 
						{
							$t_nominaemployeeaccount                     = new App\NominaEmployeeAccounts();
							$t_nominaemployeeaccount->idvacationPremium  = $t_vacationpremium->idvacationPremium;
							$t_nominaemployeeaccount->idemployeeAccounts = $request->vacationpremium_idemployeeAccounts[$i];
							$t_nominaemployeeaccount->save();
						}
					}
					else
					{
						$t_vacationpremium->idpaymentMethod = 2;
					}
				}
				else
				{
					$t_vacationpremium->idpaymentMethod = $request->vacationpremium_idpaymentMethod;
				}
				$t_vacationpremium->save();
				$t_nominaemployee              = App\NominaEmployee::find($t_vacationpremium->idnominaEmployee);
				$t_nominaemployee->worked_days = $request->vacationpremium_workedDays;
				$t_nominaemployee->save();
				$totalRequest = 0;
				$t_nomina     = App\Nomina::find($t_nominaemployee->idnomina);
				foreach ($t_nomina->nominaEmployee as $n) 
				{
					$totalRequest += $n->vacationPremium->first()->netIncome;
				}

				$t_nomina->amount = $totalRequest;
				$t_nomina->save();
				break;
			case '006':
				$t_profitsharing                       = App\ProfitSharing::find($id);
				$t_profitsharing->sd                   = $request->profitsharing_sd;
				$t_profitsharing->sdi                  = $request->profitsharing_sdi;
				$t_profitsharing->workedDays           = $request->profitsharing_workedDays;
				$t_profitsharing->totalSalary          = $request->profitsharing_totalSalary;
				$t_profitsharing->ptuForDays           = $request->profitsharing_ptuForDays;
				$t_profitsharing->ptuForSalary         = $request->profitsharing_ptuForSalary;
				$t_profitsharing->totalPtu             = $request->profitsharing_totalPtu;
				$t_profitsharing->exemptPtu            = $request->profitsharing_exemptPtu;
				$t_profitsharing->taxedPtu             = $request->profitsharing_taxedPtu;
				$t_profitsharing->totalPerceptions     = $request->profitsharing_totalPerceptions;
				$t_profitsharing->isrRetentions        = $request->profitsharing_isrRetentions;
				$t_profitsharing->totalRetentions      = $request->profitsharing_totalRetentions;
				$t_profitsharing->netIncome            = $request->profitsharing_netIncome;
				$t_profitsharing->alimony              = $request->profitsharing_alimony;
				$t_profitsharing->idAccountBeneficiary = $request->profitsharing_idAccountBeneficiary; 
				$deleteAccounts                        = App\NominaEmployeeAccounts::where('idprofitSharing',$id)->delete();
				$t_nominaemployee                      = App\NominaEmployee::find($t_profitsharing->idnominaEmployee);
				if ($request->profitsharing_idpaymentMethod == 1)
				{
					if ($request->profitsharing_idemployeeAccounts == '' && $t_nominaemployee->employee->first()->bankData()->where('visible',1)->exists()) 
					{
						$t_profitsharing->idpaymentMethod            = $request->profitsharing_idpaymentMethod;
						$t_nominaemployeeaccount                     = new App\NominaEmployeeAccounts();
						$t_nominaemployeeaccount->idprofitSharing    = $t_profitsharing->idprofitSharing;
						$t_nominaemployeeaccount->idemployeeAccounts = $t_nominaemployee->employee->first()->bankData->where('visible',1)->first()->id;
						$t_nominaemployeeaccount->save();
					}
					elseif ($request->profitsharing_idemployeeAccounts != '') 
					{
						$t_profitsharing->idpaymentMethod = $request->profitsharing_idpaymentMethod;
						for ($i=0; $i < count($request->profitsharing_idemployeeAccounts); $i++) 
						{
							$t_nominaemployeeaccount                     = new App\NominaEmployeeAccounts();
							$t_nominaemployeeaccount->idprofitSharing    = $t_profitsharing->idprofitSharing;
							$t_nominaemployeeaccount->idemployeeAccounts = $request->profitsharing_idemployeeAccounts[$i];
							$t_nominaemployeeaccount->save();
						}
					}
					else
					{
						$t_profitsharing->idpaymentMethod = 2;
					}
				}
				else
				{
					$t_profitsharing->idpaymentMethod = $request->profitsharing_idpaymentMethod;
				}
				$t_profitsharing->save();
				$t_nominaemployee = App\NominaEmployee::find($t_profitsharing->idnominaEmployee);
				$totalRequest     = 0;
				$t_nomina         = App\Nomina::find($t_nominaemployee->idnomina);
				foreach ($t_nomina->nominaEmployee as $n) 
				{
					$totalRequest += $n->profitSharing->first()->netIncome;
				}
				$t_nomina->amount = $totalRequest;
				$t_nomina->save();
				break;
		}
		$folio = $request->folio;
		$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
		$r     = App\RequestModel::find($request->folio);
		switch ($r->status) 
		{
			case 2:
				return redirect()->route('nomina.nomina-create',['id'=>$folio])->with('alert',$alert);
				break;
			case 3:
				return redirect()->route('nomina.nomina-review',['id'=>$folio])->with('alert',$alert);
				break;
			case 15:
				return redirect()->route('nomina.nomina-authorization',['id'=>$folio])->with('alert',$alert);
				break;
			case 14:
				return redirect()->route('nomina.nomina-constructionreview',['id'=>$folio])->with('alert',$alert);
				break;
			case 4:
				return redirect()->route('nomina.nomina-authorization',['id'=>$folio])->with('alert',$alert);
				break;
		}
	}

	public function getDataEmployeeF(Request $request)
	{
		if ($request->ajax()) 
		{
			$folio          = $request->folio;
			$nominaemployee = App\NominaEmployee::find($request->idnominaEmployee);
			$idtypepayroll  = $request->idtypepayroll;
			switch ($idtypepayroll)
			{
				case '001': //sueldo
					return view('administracion.nomina.modal.sueldo',['nominaemployee' => $nominaemployee,'folio' => $folio, 'idtypepayroll' => $idtypepayroll]);
					break;
				case '002': // aguinaldo
					return view('administracion.nomina.modal.aguinaldo',['nominaemployee' => $nominaemployee,'folio' => $folio, 'idtypepayroll' => $idtypepayroll]);
					break;
				case '003': //finiquito
				case '004': // liquidación
					return view('administracion.nomina.modal.liquidacion',['nominaemployee' => $nominaemployee,'folio' => $folio, 'idtypepayroll' => $idtypepayroll]);
					break;
				case '005': //prima vacacional
					return view('administracion.nomina.modal.prima-vacacional',['nominaemployee' => $nominaemployee,'folio' => $folio, 'idtypepayroll' => $idtypepayroll]);
					break;
				case '006': //reparto de utilidades
					return view('administracion.nomina.modal.utilidades',['nominaemployee' => $nominaemployee,'folio' => $folio, 'idtypepayroll' => $idtypepayroll]);
					break;
			}
		}
	}

	public function getDataPaymentWay(Request $request)
	{
		if ($request->ajax()) 
		{
			$folio				= $request->folio;
			$paymentWay			= $request->paymentWay;
			$idemployeeAccount	= $request->idemployeeAccount;
			$idAccountBeneficiary = $request->idAccountBeneficiary;
			$type				= App\RequestModel::find($folio)->nominasReal->first()->idCatTypePayroll;
			$nominaemployee		= App\NominaEmployee::find($request->idnominaEmployee);

			if($request->id != '')
			{
				$employee = App\RealEmployee::find($request->id);
				return view('administracion.nomina.modal.datosf',['employee' => $employee, 'nominaemployee' => $nominaemployee,'folio' => $folio,'paymentWay'=>$paymentWay,'idemployeeAccount'=>$idemployeeAccount,'idAccountBeneficiary'=>$idAccountBeneficiary]);
			}
		}
	}

	public function changeType(Request $request)
	{
		if ($request->ajax()) 
		{
			$idnominaEmployee	= $request->idnominaEmployee;
			$nominaemployee		= App\NominaEmployee::find($idnominaEmployee);

			return view('administracion.nomina.modal.cambiotipo',['nominaemployee'=>$nominaemployee]);
		}
	}

	public function changeTypeUpdate(Request $request)
	{
		$idnomina		= App\NominaEmployee::where('idnominaEmployee',$request->idnominaEmployee_change)->first()->idnomina;
		$folio			= App\Nomina::where('idnomina',$idnomina)->first()->idFolio;
		$idprenomina	= App\RequestModel::find($folio)->idprenomina;
		
		$soliTipo		= App\RequestModel::find($folio)->taxPayment;
		$soliCat		= App\RequestModel::find($folio)->idDepartment;

		$request->type_change	== 1 ? $empCat = 11 : $empCat = 4;
		if ($request->fiscal_change == 1) 
		{
			$empTipo = 1;
		}
		elseif ($request->fiscal_change == 2) 
		{
			$empTipo = 0;
		}
		else
		{
			$empTipo = 3;
		}

		if ($soliCat == $empCat && $soliTipo == $empTipo) 
		{
			$alert    = "swal('', 'Los datos son iguales', 'error');";
			return redirect()->route('nomina.nomina-create',['id'=>$folio])->with('alert',$alert);
		}
		else
		{
			if ($empCat == $soliCat) 
			{
				if ($empTipo == 3) 
				{
					$t_request = App\RequestModel::where('idDepartment',$soliCat)
								->whereNotIn('taxPayment',[$soliTipo])
								->where('idprenomina',$idprenomina)
								->where('status',2)
								->where('kind',16)
								->get();

					if (count($t_request)>0) 
					{
						$t_request->first()->taxPayment == 1 ? $type_new = 1 : $type_new = 2;
						$idnomina_new							= App\Nomina::where('idFolio',$t_request->first()->folio)->first()->idnomina;

						$t_nominaemployee_new					= new App\NominaEmployee();
						$t_nominaemployee_new->visible			= 1;
						$t_nominaemployee_new->idrealEmployee	= $request->idrealEmployee_change;
						$t_nominaemployee_new->idworkingData	= $request->idworkingData_change;
						$t_nominaemployee_new->type				= $request->type_change;
						$t_nominaemployee_new->fiscal			= $type_new;
						$t_nominaemployee_new->idnomina			= $idnomina_new;
						$t_nominaemployee_new->save();

						$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
						return redirect()->route('nomina.nomina-create',['id'=>$folio])->with('alert',$alert);
					}
					else
					{
						$soliTipo == 1 ? $type_new = 2 : $type_new = 1;
						$t_prenomina        = new App\Prenomina();
						$t_prenomina->date	= Carbon::now();
						$t_prenomina->user_id = Auth::user()->id;
						$t_prenomina->save();

						$idprenomina_new 	= $t_prenomina->idprenomina;

						$t_request_new					= new App\RequestModel();
						$t_request_new->kind			= 16;
						$t_request_new->fDate			= Carbon::now();
						$t_request_new->status			= 2;
						$t_request_new->idRequest		= $request->userid;
						$t_request_new->idElaborate		= Auth::user()->id;
						$t_request_new->idDepartment	= $empCat;
						if ($soliTipo == 1) 
						{
							$t_request_new->taxPayment	= 0;
						}
						else
						{
							$t_request_new->taxPayment	= 1;
						}
						$t_request_new->idprenomina		= $idprenomina_new;
						$t_request_new->save();
						$folio_new						= $t_request_new->folio;
						$kind_new						= $t_request_new->kind;

						$t_nomina					= new App\Nomina();
						$t_nomina->title			= $request->title;
						$t_nomina->datetitle		= $request->datetitle != '' ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
						$t_nomina->idFolio			= $folio_new;
						$t_nomina->idKind			= $kind_new;
						$t_nomina->idCatTypePayroll	= $request->type_payroll;
						$t_nomina->save();

						$idnomina_new 			= $t_nomina->idnomina;

						$t_nominaemployee_new					= new App\NominaEmployee();
						$t_nominaemployee_new->visible			= 1;
						$t_nominaemployee_new->idrealEmployee	= $request->idrealEmployee_change;
						$t_nominaemployee_new->idworkingData	= $request->idworkingData_change;
						$t_nominaemployee_new->type				= $request->type_change;
						$t_nominaemployee_new->fiscal			= $type_new;
						$t_nominaemployee_new->idnomina			= $idnomina_new;
						$t_nominaemployee_new->save();

						$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
						return redirect()->route('nomina.nomina-create',['id'=>$folio])->with('alert',$alert);
					}

				}
				else
				{
					$t_request = App\RequestModel::where('idDepartment',$soliCat)
								->whereNotIn('taxPayment',[$soliTipo])
								->where('idprenomina',$idprenomina)
								->where('status',2)
								->where('kind',16)
								->get();

					if (count($t_request)>0) 
					{
						$idnomina_new               = App\Nomina::where('idFolio',$t_request->first()->folio)->first()->idnomina;

						$t_nominaemployee			= App\NominaEmployee::find($request->idnominaEmployee_change);
						$t_nominaemployee->type		= $request->type_change;
						$t_nominaemployee->fiscal	= $request->fiscal_change;
						$t_nominaemployee->idnomina	= $idnomina_new;
						$t_nominaemployee->save();

						$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
						return redirect()->route('nomina.nomina-create',['id'=>$folio])->with('alert',$alert);
					}
					else
					{
						$t_prenomina        = new App\Prenomina();
						$t_prenomina->date	= Carbon::now();
						$t_prenomina->user_id = Auth::user()->id;
						$t_prenomina->save();

						$idprenomina_new 	= $t_prenomina->idprenomina;

						$t_request_new					= new App\RequestModel();
						$t_request_new->kind			= 16;
						$t_request_new->fDate			= Carbon::now();
						$t_request_new->status			= 2;
						$t_request_new->idRequest		= $request->userid;
						$t_request_new->idElaborate		= Auth::user()->id;
						$t_request_new->idDepartment	= $empCat;
						$t_request_new->taxPayment		= $empTipo;
						$t_request_new->idprenomina		= $idprenomina_new;
						$t_request_new->save();
						$folio_new						= $t_request_new->folio;
						$kind_new						= $t_request_new->kind;

						$t_nomina					= new App\Nomina();
						$t_nomina->title			= $request->title;
						$t_nomina->datetitle		= $request->datetitle != '' ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
						$t_nomina->idFolio			= $folio_new;
						$t_nomina->idKind			= $kind_new;
						$t_nomina->idCatTypePayroll	= $request->type_payroll;
						$t_nomina->save();

						$idnomina_new 			= $t_nomina->idnomina;

						$t_nominaemployee			= App\NominaEmployee::find($request->idnominaEmployee_change);
						$t_nominaemployee->type		= $request->type_change;
						$t_nominaemployee->fiscal	= $request->fiscal_change;
						$t_nominaemployee->idnomina	= $idnomina_new;
						$t_nominaemployee->save();
						$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
						return redirect()->route('nomina.nomina-create',['id'=>$folio])->with('alert',$alert);
					}
				}
			}
			else
			{
				if ($empTipo == 3) 
				{
					$t_request_fiscal = App\RequestModel::whereNotIn('idDepartment',[$soliCat])
										->where('taxPayment',1)
										->where('idprenomina',$idprenomina)
										->where('status',2)
										->where('kind',16)
										->get();

					if (count($t_request_fiscal)>0) 
					{
						//return 'hay solicitudes fiscales';
						$idnomina_new               = App\Nomina::where('idFolio',$t_request_fiscal->first()->folio)->first()->idnomina;

						$t_nominaemployee			= App\NominaEmployee::find($request->idnominaEmployee_change);
						$t_nominaemployee->visible	= 0;
						$t_nominaemployee->save();

						$t_nominaemployee_new					= new App\NominaEmployee();
						$t_nominaemployee_new->visible			= 1;
						$t_nominaemployee_new->idrealEmployee	= $request->idrealEmployee_change;
						$t_nominaemployee_new->idworkingData	= $request->idworkingData_change;
						$t_nominaemployee_new->type				= $request->type_change;
						$t_nominaemployee_new->fiscal			= 1;
						$t_nominaemployee_new->idnomina			= $idnomina_new;
						$t_nominaemployee_new->save();

					}
					else
					{
						//return 'NO hay solicitudes fiscales';
						$t_prenomina        = new App\Prenomina();
						$t_prenomina->date	= Carbon::now();
						$t_prenomina->user_id = Auth::user()->id;
						$t_prenomina->save();

						$idprenomina_new 	= $t_prenomina->idprenomina;

						$t_request_new					= new App\RequestModel();
						$t_request_new->kind			= 16;
						$t_request_new->fDate			= Carbon::now();
						$t_request_new->status			= 2;
						$t_request_new->idRequest		= $request->userid;
						$t_request_new->idElaborate		= Auth::user()->id;
						$t_request_new->idDepartment	= $empCat;
						$t_request_new->taxPayment		= 1;
						$t_request_new->idprenomina		= $idprenomina_new;
						$t_request_new->save();
						$folio_new						= $t_request_new->folio;
						$kind_new						= $t_request_new->kind;

						$t_nomina					= new App\Nomina();
						$t_nomina->title			= $request->title;
						$t_nomina->datetitle		= $request->datetitle != '' ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
						$t_nomina->idFolio			= $folio_new;
						$t_nomina->idKind			= $kind_new;
						$t_nomina->idCatTypePayroll	= $request->type_payroll;
						$t_nomina->save();

						$idnomina_new 			= $t_nomina->idnomina;

						$t_nominaemployee			= App\NominaEmployee::find($request->idnominaEmployee_change);
						$t_nominaemployee->type		= 0;
						$t_nominaemployee->save();

						$t_nominaemployee_new					= new App\NominaEmployee();
						$t_nominaemployee_new->visible			= 1;
						$t_nominaemployee_new->idrealEmployee	= $request->idrealEmployee_change;
						$t_nominaemployee_new->idworkingData	= $request->idworkingData_change;
						$t_nominaemployee_new->type				= $request->type_change;
						$t_nominaemployee_new->fiscal			= 1;
						$t_nominaemployee_new->idnomina			= $idnomina_new;
						$t_nominaemployee_new->save();
					}

					$t_request_nofiscal = App\RequestModel::whereNotIn('idDepartment',[$soliCat])
										->where('taxPayment',0)
										->where('idprenomina',$idprenomina)
										->where('status',2)
										->where('kind',16)
										->get();

					if (count($t_request_nofiscal)>0) 
					{
						//return 'hay solicitudes no fiscales';
						$idnomina_new               = App\Nomina::where('idFolio',$t_request_nofiscal->first()->folio)->first()->idnomina;

						$t_nominaemployee			= App\NominaEmployee::find($request->idnominaEmployee_change);
						$t_nominaemployee->visible	= 0;
						$t_nominaemployee->save();

						$t_nominaemployee_new					= new App\NominaEmployee();
						$t_nominaemployee_new->visible			= 1;
						$t_nominaemployee_new->idrealEmployee	= $request->idrealEmployee_change;
						$t_nominaemployee_new->idworkingData	= $request->idworkingData_change;
						$t_nominaemployee_new->type				= $request->type_change;
						$t_nominaemployee_new->fiscal			= 2;
						$t_nominaemployee_new->idnomina			= $idnomina_new;
						$t_nominaemployee_new->save();
					}
					else
					{
						//return 'NO hay solicitudes no fiscales';
						$t_prenomina        = new App\Prenomina();
						$t_prenomina->date	= Carbon::now();
						$t_prenomina->user_id = Auth::user()->id;
						$t_prenomina->save();

						$idprenomina_new 	= $t_prenomina->idprenomina;

						$t_request_new					= new App\RequestModel();
						$t_request_new->kind			= 16;
						$t_request_new->fDate			= Carbon::now();
						$t_request_new->status			= 2;
						$t_request_new->idRequest		= $request->userid;
						$t_request_new->idElaborate		= Auth::user()->id;
						$t_request_new->idDepartment	= $empCat;
						$t_request_new->taxPayment		= 0;
						$t_request_new->idprenomina		= $idprenomina_new;
						$t_request_new->save();
						$folio_new						= $t_request_new->folio;
						$kind_new						= $t_request_new->kind;

						$t_nomina					= new App\Nomina();
						$t_nomina->title			= $request->title;
						$t_nomina->datetitle		= $request->datetitle != '' ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
						$t_nomina->idFolio			= $folio_new;
						$t_nomina->idKind			= $kind_new;
						$t_nomina->idCatTypePayroll	= $request->type_payroll;
						$t_nomina->save();

						$idnomina_new 			= $t_nomina->idnomina;

						$t_nominaemployee			= App\NominaEmployee::find($request->idnominaEmployee_change);
						$t_nominaemployee->type		= 0;
						$t_nominaemployee->save();

						$t_nominaemployee_new					= new App\NominaEmployee();
						$t_nominaemployee_new->visible			= 1;
						$t_nominaemployee_new->idrealEmployee	= $request->idrealEmployee_change;
						$t_nominaemployee_new->idworkingData	= $request->idworkingData_change;
						$t_nominaemployee_new->type				= $request->type_change;
						$t_nominaemployee_new->fiscal			= 2;
						$t_nominaemployee_new->idnomina			= $idnomina_new;
						$t_nominaemployee_new->save();
					}
					$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
					return redirect()->route('nomina.nomina-create',['id'=>$folio])->with('alert',$alert);
				}
				else
				{
					$t_request = App\RequestModel::whereNotIn('idDepartment',[$soliCat])
								->where('taxPayment',[$empTipo])
								->where('idprenomina',$idprenomina)
								->where('status',2)
								->where('kind',16)
								->get();

					if (count($t_request)>0) 
					{
						$idnomina_new               = App\Nomina::where('idFolio',$t_request->first()->folio)->first()->idnomina;

						$t_nominaemployee			= App\NominaEmployee::find($request->idnominaEmployee_change);
						$t_nominaemployee->type		= $request->type_change;
						$t_nominaemployee->fiscal	= $request->fiscal_change;
						$t_nominaemployee->idnomina	= $idnomina_new;
						$t_nominaemployee->save();

						$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
						return redirect()->route('nomina.nomina-create',['id'=>$folio])->with('alert',$alert);

					}
					else
					{
						$t_prenomina        = new App\Prenomina();
						$t_prenomina->date	= Carbon::now();
						$t_prenomina->user_id = Auth::user()->id;
						$t_prenomina->save();

						$idprenomina_new 	= $t_prenomina->idprenomina;

						$t_request_new					= new App\RequestModel();
						$t_request_new->kind			= 16;
						$t_request_new->fDate			= Carbon::now();
						$t_request_new->status			= 2;
						$t_request_new->idRequest		= $request->userid;
						$t_request_new->idElaborate		= Auth::user()->id;
						$t_request_new->idDepartment	= $empCat;
						$t_request_new->taxPayment		= $empTipo;
						$t_request_new->idprenomina		= $idprenomina_new;
						$t_request_new->save();
						$folio_new						= $t_request_new->folio;
						$kind_new						= $t_request_new->kind;

						$t_nomina					= new App\Nomina();
						$t_nomina->title			= $request->title;
						$t_nomina->datetitle		= $request->datetitle != '' ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
						$t_nomina->idFolio			= $folio_new;
						$t_nomina->idKind			= $kind_new;
						$t_nomina->idCatTypePayroll	= $request->type_payroll;
						$t_nomina->save();

						$idnomina_new 			= $t_nomina->idnomina;

						$t_nominaemployee			= App\NominaEmployee::find($request->idnominaEmployee_change);
						$t_nominaemployee->type		= $request->type_change;
						$t_nominaemployee->fiscal	= $request->fiscal_change;
						$t_nominaemployee->idnomina	= $idnomina_new;
						$t_nominaemployee->save();
						$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
						return redirect()->route('nomina.nomina-create',['id'=>$folio])->with('alert',$alert);
					}
				}
			}
		}
	}

	public function viewData(Request $request)
	{
		if ($request->ajax()) 
		{

			$folio 			= $request->folio;
			$type			= App\RequestModel::find($folio)->taxPayment;
			$nominaemployee	= App\NominaEmployee::find($request->idnominaEmployee);

			if($request->id != '')
			{
				if ($type == 0) //PARA SOLICITUDES NO FISCALES
				{
					$employee = App\RealEmployee::find($request->id);
					return view('administracion.nomina.modal.verdatos',['employee' => $employee, 'nominaemployee' => $nominaemployee,'folio' => $folio]);
				}
				if ($type == 1) //PARA SOLICITUDES FISCALES
				{
					$employee = App\RealEmployee::find($request->id);
					return view('administracion.nomina.modal.datosf',['employee' => $employee, 'nominaemployee' => $nominaemployee,'folio' => $folio]);
				}
			}
		}
	}

	public function updateNomina(Request $request,$id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$daysYear    = date("L") == 1 ? 366 : 365;
			$t_request   = App\RequestModel::find($id);
			$checkFiscal = App\RequestModel::where('kind',16)
				->where('idprenomina',$t_request->idprenomina)
				->where('idDepartment',$t_request->idDepartment)
				->where('taxPayment',1)
				->whereNotIn('folio',[$id])
				->first();
			$flagCheckFiscal = false;
			if($checkFiscal != '') 
			{
				if ($checkFiscal->idDepartment == 4)
				{
					if ($checkFiscal->status == 4 || $checkFiscal->status == 5 || $checkFiscal->status == 10 || $checkFiscal->status == 12 || $checkFiscal->status == 11)
					{
						$flagCheckFiscal = true;
					}
					else
					{
						$flagCheckFiscal = false;
						$alert           = 'swal("","La solicitud no puede ser enviada debido a que la solicitud Fiscal no ha sido revisada.","error");';
						return back()->with('alert',$alert);
					}
				}
				if ($checkFiscal->idDepartment == 11)
				{
					if ($checkFiscal->status == 14 || $checkFiscal->status == 15 || $checkFiscal->status == 5 || $checkFiscal->status == 10 || $checkFiscal->status == 12 || $checkFiscal->status == 11)
					{
						$flagCheckFiscal = true;
					}
					else
					{
						$flagCheckFiscal = false;
						$alert           = 'swal("","La solicitud no puede ser enviada debido a que la solicitud Fiscal no ha sido revisada.","error");';
						return back()->with('alert',$alert);
					}
				}
			}
			else
			{
				$flagCheckFiscal = true;
			}
			if($flagCheckFiscal)
			{
				$errors_nofiscal = [];
				$request_type = App\RequestModel::find($id);
				if($request_type->taxPayment == 0)
				{
					for($i = 0; $i < count($request->request_idnominaemployeenf); $i++)
					{
						$t_nominaemployee                   = App\NominaEmployee::find($request->request_idnominaEmployee[$i]);
						$t_nominaemployee->from_date        = $request->from_date_request	!= "" ? Carbon::createFromFormat('d-m-Y',$request->from_date_request)->format('Y-m-d')	: null;
						$t_nominaemployee->to_date          = $request->to_date_request		!= "" ? Carbon::createFromFormat('d-m-Y',$request->to_date_request)->format('Y-m-d') 	: null;
						$t_nominaemployee->idCatPeriodicity = $request->periodicity_request;
						if ($request->type_payroll == '001') 
						{
							$t_nominaemployee->absence          = $request->absence[$i];
							$t_nominaemployee->extra_hours      = $request->extraHours[$i];
							$t_nominaemployee->holidays         = $request->holidays[$i];
							$t_nominaemployee->sundays          = $request->sundays[$i];
						}
						$t_nominaemployee->save();
					}

					for($i = 0; $i < count($request->request_idnominaemployeenf); $i++)
					{
						$t_nominaemployee 	= App\NominaEmployee::find($request->request_idnominaEmployee[$i]);

						if($request->request_paymentWay[$i] == '' || $request->request_paymentWay[$i] == null)
						{
							$errors_nofiscal[] = $request->request_idnominaemployeenf[$i];
						}
						if($t_request->nominasReal->first()->type_nomina == 2)
						{
							if($request->request_paymentWay[$i] == "1" && ($request->request_idemployeeAccount[$i] == '' || $request->request_idemployeeAccount[$i] == null))
							{
								$errors_nofiscal[] = $request->request_idnominaemployeenf[$i];
							}
						}
					}

					if(count($errors_nofiscal) > 0)
					{
						$alert = 'swal("","Verifique los empleados marcados en rojo. Que los empleados tengan una: \n * Forma de pago \n * Cuenta dada de alta o cambie la forma de pago a Efectivo.","error");';
						return redirect()->route('nomina.nomina-create',['id'=> $id])->with(['alert'=> $alert,'errors_nofiscal' => $errors_nofiscal]);
					}
				}
				if($request_type->taxPayment == 1)
				{
					//** Validación Tipo Régimen y Tipo Contrato **/
					$tipoContrato = ['01','02','03','04','05','06','07','08'];
					$tipoRegimen = ['02','03','04'];
					for ($i = 0; $i < count($request->idnominaEmployee_request); $i++)
					{
						$t_nominaemployee = App\NominaEmployee::find($request->idnominaEmployee_request[$i]);

						if ($request->type_payroll == '001') 
						{
							$t_nominaemployee->from_date        = $request->from_date[$i]	!= "" ? Carbon::createFromFormat('d-m-Y',$request->from_date[$i])->format('Y-m-d')	: null;
							$t_nominaemployee->to_date          = $request->to_date[$i]		!= "" ? Carbon::createFromFormat('d-m-Y',$request->to_date[$i])->format('Y-m-d')	: null;
							$t_nominaemployee->idCatPeriodicity = $request->periodicity[$i];
							$t_nominaemployee->absence          = $request->absence[$i];
							$t_nominaemployee->extra_hours      = $request->extraHours[$i];
							$t_nominaemployee->holidays         = $request->holidays[$i];
							$t_nominaemployee->sundays          = $request->sundays[$i];

							$nom_no_fiscal 	= App\RequestModel::where('kind',16)
											->where('idprenomina',$t_request->idprenomina)
											->where('idDepartment',$t_request->idDepartment)
											->where('taxPayment',0)
											->get();

							if($nom_no_fiscal != '')
							{
								foreach ($nom_no_fiscal as $request_nf) 
								{
									$nom_emp_nf = App\NominaEmployee::where('idrealEmployee',$t_nominaemployee->idrealEmployee)
												->where('idnomina',$request_nf->nominasReal->first()->idnomina)
												->first();
									
									if ($nom_emp_nf != "") 
									{
										$nom_emp_nf->from_date        = $request->from_date[$i]	!= "" ? Carbon::createFromFormat('d-m-Y',$request->from_date[$i])->format('Y-m-d')	: null;
										$nom_emp_nf->to_date          = $request->to_date[$i]	!= "" ? Carbon::createFromFormat('d-m-Y',$request->to_date[$i])->format('Y-m-d')	: null;
										$nom_emp_nf->idCatPeriodicity = $request->periodicity[$i];
										$nom_emp_nf->absence          = $request->absence[$i];
										$nom_emp_nf->extra_hours      = $request->extraHours[$i];
										$nom_emp_nf->holidays         = $request->holidays[$i];
										$nom_emp_nf->sundays          = $request->sundays[$i];
										$nom_emp_nf->save();
									}
								}
							}	
						}
						$t_nominaemployee->save();
					}
					
					switch($request->type_payroll)
					{
						case '001':

							$errors_salary = [];
							for ($i = 0; $i < count($request->idnominaEmployee_request); $i++)
							{

								$t_nominaemployee = App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$count_days_range 	= App\Http\Controllers\AdministracionNominaController::daysPassed($request->from_date[$i],$request->to_date[$i])+1;
								$days_periodicity 	= App\CatPeriodicity::find($request->periodicity[$i])->days;								

								if($request->periodicity[$i] == "01" && ($request->absence[$i] > 1 || $request->holidays[$i] > 1  || $request->extraHours[$i] < 0 || $request->absence[$i] < 0 || $request->sundays[$i] < 0 || $request->holidays[$i] < 0))
								{
									$errors_salary[] = $request->idnominaEmployee_request[$i];
								}
								elseif($request->periodicity[$i] == "02" && ($request->absence[$i] > 7 || $request->sundays[$i] > 1 || $request->holidays[$i] > 1  || $request->extraHours[$i] < 0 || $request->absence[$i] < 0 || $request->sundays[$i] < 0 || $request->holidays[$i] < 0))
								{
									$errors_salary[] = $request->idnominaEmployee_request[$i];
								}
								elseif($request->periodicity[$i] == "03" && ($request->absence[$i] > 14 || $request->sundays[$i] > 2 || $request->holidays[$i] > 2  || $request->extraHours[$i] < 0 || $request->absence[$i] < 0 || $request->sundays[$i] < 0 || $request->holidays[$i] < 0))
								{
									$errors_salary[] = $request->idnominaEmployee_request[$i];
								}
								elseif($request->periodicity[$i] == "04" && ($request->absence[$i] > 14 || $request->sundays[$i] > 2 || $request->holidays[$i] > 2  || $request->extraHours[$i] < 0 || $request->absence[$i] < 0 || $request->sundays[$i] < 0 || $request->holidays[$i] < 0))
								{
									$errors_salary[] = $request->idnominaEmployee_request[$i];
								}
								elseif($request->periodicity[$i] == "05" && ($request->absence[$i] > 30 || $request->sundays[$i] > 4 || $request->holidays[$i] > 4  || $request->extraHours[$i] < 0 || $request->absence[$i] < 0 || $request->sundays[$i] < 0 || $request->holidays[$i] < 0))
								{
									$errors_salary[] = $request->idnominaEmployee_request[$i];
								}
								elseif($request->periodicity[$i] == "")
								{
									$errors_salary[] = $request->idnominaEmployee_request[$i];
								}
								
								if (($request->periodicity[$i] == "02" && ($count_days_range < 7 || $count_days_range > 7)) || ($request->periodicity[$i] == "04" && ($count_days_range < 14 || $count_days_range > 16)) || ($request->periodicity[$i] == "05"  && ($count_days_range < 28 || $count_days_range > 31))) 
								{
									$errors_salary[] = $request->idnominaEmployee_request[$i];
								}
								if(in_array($t_nominaemployee->workerData->first()->workerType,$tipoContrato))
								{
									if(!in_array($t_nominaemployee->workerData->first()->regime_id,$tipoRegimen))
									{
										$errors_salary[] = $request->idnominaEmployee_request[$i];
									}
								}
								else
								{
									if(in_array($t_nominaemployee->workerData->first()->regime_id,$tipoRegimen))
									{
										$errors_salary[] = $request->idnominaEmployee_request[$i];
									}
								}
								if($request->paymentWay[$i] == '' || $request->paymentWay[$i] == null)
								{
									$errors_salary[] = $request->idnominaEmployee_request[$i];
								}
								if($request->paymentWay[$i] == "1" && ($request->idemployeeAccount[$i] == '' || $request->idemployeeAccount[$i] == null))
								{
									$errors_salary[] = $request->idnominaEmployee_request[$i];
								}
							
								$t_nominaemployee       = App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$admissionDate          = $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$sdi                    = $t_nominaemployee->workerData->first()->sdi;
								$sueldoNeto             = $t_nominaemployee->workerData->first()->netIncome;
								$primaDeRiesgoDeTrabajo = isset(App\EmployerRegister::where('employer_register',$t_nominaemployee->workerData->first()->employer_register)->first()->risk_number) ? App\EmployerRegister::where('employer_register',$t_nominaemployee->workerData->first()->employer_register)->first()->risk_number : '';

								if($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null || $sdi == 0 || $primaDeRiesgoDeTrabajo == '' || $primaDeRiesgoDeTrabajo == null)
								{
									$errors_salary[] = $request->idnominaEmployee_request[$i];
								}
								$calculations = [];
								$calculations['admissionDate']  = $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								if (new \DateTime($request->from_date[$i]) < new \DateTime($calculations['admissionDate'])) 
								{
									$datetime1 = date_create($request->from_date[$i]);
									$datetime2 = date_create($calculations['admissionDate']);
									$interval  = date_diff($datetime1, $datetime2);
									$daysStart = $interval->format('%a');
								}
								else
								{
									$daysStart = 0;
								}
								$downDate = $t_nominaemployee->workerData->first()->downDate != '' && new \DateTime($t_nominaemployee->workerData->first()->downDate) > new \DateTime($calculations['admissionDate']) ? $t_nominaemployee->workerData->first()->downDate : null;
								$daysDown = 0;
								if ($downDate !='' && new \DateTime($downDate) >= new \DateTime($request->from_date[$i]) && new \DateTime($downDate) <= new \DateTime($request->to_date[$i])) 
								{
									$date1    = new \DateTime($downDate);
									$date2    = new \DateTime($request->to_date[$i]);
									$diff     = $date1->diff($date2);
									$daysDown = $diff->days;
								}
								else
								{
									$daysDown = 0;
								}
								$calculations['workedDays']  = (App\CatPeriodicity::find($request->periodicity[$i])->days)-$request->absence[$i]-$daysStart-$daysDown;

								if ($calculations['workedDays'] > 0) 
								{
									$t_nominaemployee->worked_days = $calculations['workedDays'];
									$t_nominaemployee->save();
								}

								if ($calculations['workedDays'] < 1) 
								{
									$errors_salary[] = $request->idnominaEmployee_request[$i];
								}
							}
							
							if(count($errors_salary) > 0)
							{
								$alert = 'swal("","Verifique los empleados marcados en rojo. Que los siguientes datos que correspondan a la periodicidad del empleado: \n * Domingos trabajados \n * Faltas \n * Días festivos \n \n Verifique que el empleado tenga los siguientes datos \n * Forma de Pago \n * Tipo de contrato \n * Días trabajados \n * SDI","error");';
								return redirect()->route('nomina.nomina-create',['id'=> $id])->with(['alert'=> $alert,'errors_salary' => $errors_salary]);
							}
							break;
						case '002':
							$errors_bonus = [];
							for($i = 0; $i < count($request->idnominaEmployee_request); $i++)
							{
								$t_nominaemployee  = App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$paymentWay        = $request->paymentWay[$i];
								$idemployeeAccount = $request->idemployeeAccount[$i];
								//calculo para dias de vacaciones
								$admissionDate     = $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$sdi               = $t_nominaemployee->workerData->first()->sdi;
								$primaDeRiesgoDeTrabajo = isset(App\EmployerRegister::where('employer_register',$t_nominaemployee->workerData->first()->employer_register)->first()->risk_number) ? App\EmployerRegister::where('employer_register',$t_nominaemployee->workerData->first()->employer_register)->first()->risk_number : '';
								if ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null || $paymentWay == null || $paymentWay == '' || $primaDeRiesgoDeTrabajo == '' || $primaDeRiesgoDeTrabajo == null) 
								{
									$errors_bonus[] = $request->idnominaEmployee_request[$i];
								}
								if ($paymentWay == 1 && ($idemployeeAccount == '' || $idemployeeAccount== null)) 
								{
									$errors_bonus[] = $request->idnominaEmployee_request[$i];
								}
								if ($paymentWay == 2 && ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null))
								{
									$errors_bonus[] = $request->idnominaEmployee_request[$i];
								}
							}

							if(count($errors_bonus) > 0)
							{
								$alert = 'swal("","Verifique los empleados marcados en rojo. Que los empleados cuenten con los siguientes datos: \n *Fecha de Alta \n * SDI \n * Forma de pago \n * Cuenta bancaria ó cambiar la forma de pago a Efectivo.","error");';
								return redirect()->route('nomina.nomina-create',['id'=> $id])->with(['alert'=> $alert,'errors_bonus' => $errors_bonus]);
							}
							break;
						case '003':
						case '004':
							$errors_settlement = [];
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee  = App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$paymentWay        = $request->paymentWay[$i];
								$idemployeeAccount = $request->idemployeeAccount[$i];
								$admissionDate     = $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$sdi               = $t_nominaemployee->workerData->first()->sdi;
								$primaDeRiesgoDeTrabajo = isset(App\EmployerRegister::where('employer_register',$t_nominaemployee->workerData->first()->employer_register)->first()->risk_number) ? App\EmployerRegister::where('employer_register',$t_nominaemployee->workerData->first()->employer_register)->first()->risk_number : '';
								if ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null || $paymentWay == null || $paymentWay == '' ||  $primaDeRiesgoDeTrabajo == '' || $primaDeRiesgoDeTrabajo == null) 
								{
									$errors_settlement[] = $request->idnominaEmployee_request[$i];
								}
								if ($paymentWay == 1 && ($idemployeeAccount == '' || $idemployeeAccount== null)) 
								{
									$errors_settlement[] = $request->idnominaEmployee_request[$i];
								}
								if ($paymentWay == 2 && ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null))
								{
									$errors_settlement[] = $request->idnominaEmployee_request[$i];
								}
							}

							if(count($errors_settlement) > 0)
							{
								$alert = 'swal("","Verifique los empleados marcados en rojo. Que los empleados cuenten con los siguientes datos: \n *Fecha de Alta \n * SDI \n * Forma de pago \n * Cuenta bancaria ó cambiar la forma de pago a Efectivo.","error");';
								return redirect()->route('nomina.nomina-create',['id'=> $id])->with(['alert'=> $alert,'errors_settlement' => $errors_settlement]);
							}

							break;

						case '005':
							$errors_holidaypremium = [];
							for($i = 0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee  = App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$admissionDate     = $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$sdi               = $t_nominaemployee->workerData->first()->sdi;
								$primaDeRiesgoDeTrabajo = isset(App\EmployerRegister::where('employer_register',$t_nominaemployee->workerData->first()->employer_register)->first()->risk_number) ? App\EmployerRegister::where('employer_register',$t_nominaemployee->workerData->first()->employer_register)->first()->risk_number : '';
								$paymentWay        = $request->paymentWay[$i];
								$idemployeeAccount = $request->idemployeeAccount[$i];
								if($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null || $paymentWay == null || $paymentWay == '' ||  $primaDeRiesgoDeTrabajo == '' || $primaDeRiesgoDeTrabajo == null) 
								{
									$errors_holidaypremium[] = $request->idnominaEmployee_request[$i];
								}
								if($paymentWay == 1 && ($idemployeeAccount == '' || $idemployeeAccount== null)) 
								{
									$errors_holidaypremium[] = $request->idnominaEmployee_request[$i];
								}
								if($paymentWay == 2 && ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null))
								{
									$errors_holidaypremium[] = $request->idnominaEmployee_request[$i];
								}
							}

							if(count($errors_holidaypremium) > 0)
							{
								$alert = 'swal("","Verifique los empleados marcados en rojo. Que los empleados cuenten con los siguientes datos: \n *Fecha de Alta \n * SDI \n * Forma de pago \n * Cuenta bancaria ó cambiar la forma de pago a Efectivo.","error");';
								return redirect()->route('nomina.nomina-create',['id'=> $id])->with(['alert'=> $alert,'errors_holidaypremium' => $errors_holidaypremium]);
							}
							break;

						case '006':
							$errors_profitsharing = [];
							for($i = 0; $i < count($request->idnominaEmployee_request); $i++) 
							{
								$t_nominaemployee       = App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$paymentWay             = $request->paymentWay[$i];
								$idemployeeAccount      = $request->idemployeeAccount[$i];
								$admissionDate          = $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$sdi                    = $t_nominaemployee->workerData->first()->sdi;
								$primaDeRiesgoDeTrabajo = isset(App\EmployerRegister::where('employer_register',$t_nominaemployee->workerData->first()->employer_register)->first()->risk_number) ? App\EmployerRegister::where('employer_register',$t_nominaemployee->workerData->first()->employer_register)->first()->risk_number : '';
								if($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null || $paymentWay == null || $paymentWay == '' ||  $primaDeRiesgoDeTrabajo == '' || $primaDeRiesgoDeTrabajo == null)
								{
									$errors_profitsharing[] = $request->idnominaEmployee_request[$i];
								}
								if($paymentWay == 2 && ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null ||  $primaDeRiesgoDeTrabajo == '' || $primaDeRiesgoDeTrabajo == null))
								{
									$errors_profitsharing[] = $request->idnominaEmployee_request[$i];
								}
							}

							if(count($errors_profitsharing) > 0)
							{
								$alert = 'swal("","Verifique los empleados marcados en rojo. Que los empleados cuenten con los siguientes datos: \n *Fecha de Alta \n * SDI \n * Forma de pago \n * Registro Patronal.","error");';
								return redirect()->route('nomina.nomina-create',['id'=> $id])->with(['alert'=> $alert,'errors_profitsharing' => $errors_profitsharing]);
							}
							break;
					}
				}
				$t_request            = App\RequestModel::find($id);
				$t_request->idRequest = $request->userid;
				if ($t_request->taxPayment == 1) 
				{
					$t_request->status = 3;
				}
				if($t_request->taxPayment == 0) 
				{
					if ($t_request->idDepartment == 11) 
					{
						$t_request->status = 14;
					}
					else
					{
						$t_request->status = 4;
					}
				}
				$t_request->save();
				$totalRequest               = 0;
				$t_nomina                   = App\Nomina::find($t_request->nominasReal->first()->idnomina);
				$t_nomina->title            = $request->title;
				$t_nomina->datetitle        = $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
				$t_nomina->idCatTypePayroll = $request->type_payroll;
				$t_nomina->from_date        = $request->from_date_request	!= "" ? Carbon::createFromFormat('d-m-Y',$request->from_date_request)->format('Y-m-d') 	: null;
				$t_nomina->to_date          = $request->to_date_request		!= "" ? Carbon::createFromFormat('d-m-Y',$request->to_date_request)->format('Y-m-d') 	: null;
				$t_nomina->down_date        = $request->down_date_request	!= "" ? Carbon::createFromFormat('d-m-Y',$request->down_date_request)->format('Y-m-d') 	: null;
				$t_nomina->idCatPeriodicity = $request->periodicity_request;
				$t_nomina->ptu_to_pay 		= $request->ptu_to_pay;
				if($t_request->taxPayment == 0) 
				{
					if($t_request->nominasReal->first()->type_nomina == 2)
					{
						if(isset($request->deleteEmployee) && $request->deleteEmployee != '')
						{
							for($i = 0; $i < count($request->deleteEmployee); $i++) 
							{
								$deleteNF = App\NominaEmployeeNF::where('idnominaEmployee',$request->deleteEmployee[$i])->delete();
								$delete   = App\NominaEmployee::find($request->deleteEmployee[$i])->delete();
							}
						}
						for($i = 0; $i < count($request->request_idnominaemployeenf); $i++) 
						{
							$t_nominaemployee                   = App\NominaEmployee::find($request->request_idnominaEmployee[$i]);
							$t_nominaemployee->from_date        = $request->from_date_request	!= "" ? Carbon::createFromFormat('d-m-Y',$request->from_date_request)->format('Y-m-d') 	: null;
							$t_nominaemployee->to_date          = $request->to_date_request		!= "" ? Carbon::createFromFormat('d-m-Y',$request->to_date_request)->format('Y-m-d') 	: null;
							$t_nominaemployee->idCatPeriodicity = $request->periodicity_request;
							$t_nominaemployee->absence          = $request->absence[$i];
							$t_nominaemployee->extra_hours      = $request->extraHours[$i];
							$t_nominaemployee->holidays         = $request->holidays[$i];
							$t_nominaemployee->sundays          = $request->sundays[$i];
							$t_nominaemployee->save();
							if ($request->request_idnominaemployeenf[$i] == 'x') 
							{
								$t_nominaemployeenf                   = new App\NominaEmployeeNF();
							}
							else
							{
								$t_nominaemployeenf						= App\NominaEmployeeNF::find($request->request_idnominaemployeenf[$i]);
								App\DiscountsNomina::where('idnominaemployeenf',$request->request_idnominaemployeenf[$i])->delete();
							}
							$t_nominaemployeenf->idnominaEmployee = $request->request_idnominaEmployee[$i];
							if($request->request_paymentWay[$i] == 1) 
							{
								if ($request->request_idemployeeAccount[$i] == '' && $t_nominaemployee->employee->first()->bankData()->where('visible',1)->exists()) 
								{
									$t_nominaemployeenf->idpaymentMethod	= $request->request_paymentWay[$i];
									$t_nominaemployeenf->idemployeeAccounts	= $t_nominaemployee->employee->first()->bankData->where('visible',1)->first()->id;
								}
								elseif ($request->request_idemployeeAccount[$i] != '')
								{
									$t_nominaemployeenf->idpaymentMethod    = $request->request_paymentWay[$i];
									$t_nominaemployeenf->idemployeeAccounts = $request->request_idemployeeAccount[$i];
								}
								else 
								{
									$t_nominaemployeenf->idpaymentMethod    = 2;
									$t_nominaemployeenf->idemployeeAccounts =  null;
								}
							}
							else
							{
								$t_nominaemployeenf->idpaymentMethod    = $request->request_paymentWay[$i];
								$t_nominaemployeenf->idemployeeAccounts = null;
							}
							$t_nominaemployeenf->reference = $request->request_reference[$i];

							if ($t_nomina->idCatTypePayroll == '001') 
							{
								$t_nominaemployeenf->extra_time			= $request->total_extra_time_no_fiscal[$i];
								$t_nominaemployeenf->holiday			= $request->total_holiday_no_fiscal[$i];
								$t_nominaemployeenf->sundays			= $request->total_sundays_no_fiscal[$i];
								$t_nominaemployeenf->complementPartial	= $request->sueldo_total_no_fiscal[$i];
								$t_nominaemployeenf->amount				= $request->total_no_fiscal_por_pagar[$i];
								$t_nominaemployeenf->netIncome    		= $request->request_netIncome[$i];
								$t_nominaemployeenf->save();
							}
							else
							{

								if($request->type_nf == 1) 
								{
									$t_nominaemployeenf->complementPartial = $request->request_netIncome[$i];
									$t_nominaemployeenf->amount            = $request->request_netIncome[$i];
								}
								else
								{
									$t_nominaemployeenf->complementPartial = $request->request_netIncome[$i];
									$t_nominaemployeenf->amount            = $request->request_netIncome[$i];

									$t_nominaemployeenf->netIncome    = $request->request_netIncome[$i];
									$t_nominaemployeenf->reasonAmount = $request->request_reason_payment[$i];
									$t_nominaemployeenf->save();
								}
							}

							if ($request->infonavit_complemento[$i] > 0) 
							{
								$t_discount                     = new App\DiscountsNomina();
								$t_discount->amount             = $request->infonavit_complemento[$i];
								$t_discount->reason             = 'INFONAVIT parte fiscal';
								$t_discount->idnominaemployeenf = $t_nominaemployeenf->idnominaemployeenf;
								$t_discount->save();
							}
							$t_nominaemployeenf->save();
						}

						foreach ($t_nomina->nominaEmployee as $n) 
						{
							$totalRequest += $n->nominasEmployeeNF->first()->amount;
						}

						$t_nomina->amount = $totalRequest;
					}
					// NOM35 
					if ($t_request->nominasReal->first()->type_nomina == 3) 
					{
						if (isset($request->deleteEmployee) && $request->deleteEmployee != '') 
						{
							for ($i=0; $i < count($request->deleteEmployee); $i++) 
							{ 
								$deleteNF	= App\NominaEmployeeNF::where('idnominaEmployee',$request->deleteEmployee[$i])->delete();
								$delete		= App\NominaEmployee::find($request->deleteEmployee[$i])->delete();
							}
						}
						for ($i=0; $i < count($request->request_idnominaemployeenf); $i++) 
						{ 
							$t_nominaemployee					= App\NominaEmployee::find($request->request_idnominaEmployee[$i]);
							$t_nominaemployee->from_date		= $request->from_date_request	!= "" ? Carbon::createFromFormat('d-m-Y',$request->from_date_request)->format('Y-m-d')	: null;
							$t_nominaemployee->to_date			= $request->to_date_request		!= "" ? Carbon::createFromFormat('d-m-Y',$request->to_date_request)->format('Y-m-d')	: null;
							$t_nominaemployee->idCatPeriodicity	= $request->periodicity_request;
							$t_nominaemployee->absence			= $request->absence[$i];
							$t_nominaemployee->extra_hours		= $request->extraHours[$i];
							$t_nominaemployee->holidays			= $request->holidays[$i];
							$t_nominaemployee->sundays			= $request->sundays[$i];
							$t_nominaemployee->save();
							if ($request->request_idnominaemployeenf[$i] == 'x') 
							{
								$t_nominaemployeenf						= new App\NominaEmployeeNF();
							}
							else
							{
								$t_nominaemployeenf						= App\NominaEmployeeNF::find($request->request_idnominaemployeenf[$i]);
								App\DiscountsNomina::where('idnominaemployeenf',$request->request_idnominaemployeenf[$i])->delete();
							}
							$t_nominaemployeenf->idnominaEmployee	= $request->request_idnominaEmployee[$i];
							
							if ($request->request_paymentWay[$i] == 1) 
							{
								if ($request->request_idemployeeAccount[$i] == '' && $t_nominaemployee->employee->first()->bankData()->where('visible',1)->exists()) 
								{
									$t_nominaemployeenf->idpaymentMethod	= $request->request_paymentWay[$i];
									$t_nominaemployeenf->idemployeeAccounts	= $t_nominaemployee->employee->first()->bankData->where('visible',1)->first()->id;
								}
								elseif ($request->request_idemployeeAccount[$i] != '')
								{
									$t_nominaemployeenf->idpaymentMethod	= $request->request_paymentWay[$i];
									$t_nominaemployeenf->idemployeeAccounts	= $request->request_idemployeeAccount[$i];
								}
								else 
								{
									$t_nominaemployeenf->idpaymentMethod	= 2;
									$t_nominaemployeenf->idemployeeAccounts	= null;
								}
							}
							else
							{
								$t_nominaemployeenf->idpaymentMethod	= $request->request_paymentWay[$i];
								$t_nominaemployeenf->idemployeeAccounts	= null;
							}

							$t_nominaemployeenf->reference			= $request->request_reference[$i];
							
							if ($t_nomina->idCatTypePayroll == '001') 
							{
								$t_nominaemployeenf->extra_time			= $request->total_extra_time_no_fiscal[$i];
								$t_nominaemployeenf->holiday			= $request->total_holiday_no_fiscal[$i];
								$t_nominaemployeenf->sundays			= $request->total_sundays_no_fiscal[$i];
								$t_nominaemployeenf->complementPartial	= $request->sueldo_total_no_fiscal[$i];
								$t_nominaemployeenf->amount				= $request->total_no_fiscal_por_pagar[$i];
								$t_nominaemployeenf->netIncome    		= $request->request_netIncome[$i];
								$t_nominaemployeenf->save();
							}
							else
							{

								if($request->type_nf == 1) 
								{
									$t_nominaemployeenf->complementPartial = $request->request_netIncome[$i];
									$t_nominaemployeenf->amount            = $request->request_netIncome[$i];
								}
								else
								{
									$t_nominaemployeenf->complementPartial = $request->request_netIncome[$i];
									$t_nominaemployeenf->amount            = $request->request_netIncome[$i];

									$t_nominaemployeenf->netIncome    = $request->request_netIncome[$i];
									$t_nominaemployeenf->reasonAmount = $request->request_reason_payment[$i];
									$t_nominaemployeenf->save();
								}
							}

							if ($request->infonavit_complemento[$i] > 0) 
							{
								$t_discount                     = new App\DiscountsNomina();
								$t_discount->amount             = $request->infonavit_complemento[$i];
								$t_discount->reason             = 'INFONAVIT parte fiscal';
								$t_discount->idnominaemployeenf = $t_nominaemployeenf->idnominaemployeenf;
								$t_discount->save();
							}
							$t_nominaemployeenf->save();
						}

						foreach ($t_nomina->nominaEmployee as $n) 
						{
							$totalRequest += $n->nominasEmployeeNF->first()->amount;
						}

						$t_nomina->amount = $totalRequest;
					}
				}
				$t_nomina->save();
				if($t_request->taxPayment == 1)
				{
					if(isset($request->deleteEmployee) && $request->deleteEmployee != '')
					{
						if (App\Salary::whereIn('idnominaEmployee',$request->deleteEmployee)->count() > 0) 
						{
							$idSalary = App\Salary::select('idSalary')->whereIn('idnominaEmployee',$request->deleteEmployee)->get();
							App\NominaEmployeeAccounts::whereIn('idSalary',$idSalary)->delete();
							App\Salary::whereIn('idnominaEmployee',$request->deleteEmployee)->delete();
						}
						if (App\Bonus::whereIn('idnominaEmployee',$request->deleteEmployee)->count() > 0) 
						{
							$idBonus = App\Bonus::select('idBonus')->whereIn('idnominaEmployee',$request->deleteEmployee)->get();
							App\NominaEmployeeAccounts::whereIn('idBonus',$idBonus)->delete();
							App\Bonus::whereIn('idnominaEmployee',$request->deleteEmployee)->delete();
						}
						if (App\Settlement::whereIn('idnominaEmployee',$request->deleteEmployee)->count() > 0) 
						{
							$idSettlement = App\Settlement::select('idSettlement')->whereIn('idnominaEmployee',$request->deleteEmployee)->get();
							App\NominaEmployeeAccounts::whereIn('idSettlement',$idSettlement)->delete();
							App\Settlement::whereIn('idnominaEmployee',$request->deleteEmployee)->delete();
						}
						if (App\Liquidation::whereIn('idnominaEmployee',$request->deleteEmployee)->count() > 0) 
						{
							$idLiquidation = App\Liquidation::select('idLiquidation')->whereIn('idnominaEmployee',$request->deleteEmployee)->get();
							App\NominaEmployeeAccounts::whereIn('idLiquidation',$idLiquidation)->delete();
							App\Liquidation::whereIn('idnominaEmployee',$request->deleteEmployee)->delete();
						}
						if (App\VacationPremium::whereIn('idnominaEmployee',$request->deleteEmployee)->count() > 0) 
						{
							$idvacationPremium = App\VacationPremium::select('idvacationPremium')->whereIn('idnominaEmployee',$request->deleteEmployee)->get();
							App\NominaEmployeeAccounts::whereIn('idvacationPremium',$idvacationPremium)->delete();
							App\VacationPremium::whereIn('idnominaEmployee',$request->deleteEmployee)->delete();
						}
						if (App\ProfitSharing::whereIn('idnominaEmployee',$request->deleteEmployee)->count() > 0) 
						{
							$idprofitSharing = App\ProfitSharing::select('idprofitSharing')->whereIn('idnominaEmployee',$request->deleteEmployee)->get();
							App\NominaEmployeeAccounts::whereIn('idprofitSharing',$idprofitSharing)->delete();
							App\ProfitSharing::whereIn('idnominaEmployee',$request->deleteEmployee)->delete();
						}

						$delete			= App\NominaEmployee::whereIn('idnominaEmployee',$request->deleteEmployee)->delete();
					}
					switch ($request->type_payroll)
					{
						case '001':
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee                   = App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$t_nominaemployee->from_date        = $request->from_date[$i]	!= "" ? Carbon::createFromFormat('d-m-Y',$request->from_date[$i])->format('Y-m-d')	: null;
								$t_nominaemployee->to_date          = $request->to_date[$i]		!= "" ? Carbon::createFromFormat('d-m-Y',$request->to_date[$i])->format('Y-m-d')	: null;
								$t_nominaemployee->idCatPeriodicity = $request->periodicity[$i];
								$t_nominaemployee->absence          = $request->absence[$i];
								$t_nominaemployee->extra_hours      = $request->extraHours[$i];
								$t_nominaemployee->holidays         = $request->holidays[$i];
								$t_nominaemployee->sundays          = $request->sundays[$i];
								$t_nominaemployee->loan_perception  = $request->loan_perception[$i];
								$t_nominaemployee->loan_retention   = $request->loan_retention[$i];
								$t_nominaemployee->save();
								$calculations = [];
								//calculo para dias de vacaciones
								$calculations['admissionDate']  = $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$calculations['nowDate']        = Carbon::now();
								$calculations['diasTrabajados'] = App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['admissionDate'],$calculations['nowDate']);
								$calculations['yearsWork']      = ceil($calculations['diasTrabajados']/365);
								if ($calculations['yearsWork'] > 24) 
								{
									$calculations['vacationDays'] = 20;
								}
								else
								{
									$calculations['vacationDays'] = App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count() > 0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
								}
								//-------------------
								$calculations['prima_vac_esp'] = App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
								$calculations['sdi']           = $t_nominaemployee->workerData->first()->sdi;
								$calculations['sd']            = round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);
								$daysStart = 0;	
								if (new \DateTime($request->from_date[$i]) < new \DateTime($calculations['admissionDate'])) 
								{
									$datetime1 = date_create($request->from_date[$i]);
									$datetime2 = date_create($calculations['admissionDate']);
									$interval  = date_diff($datetime1, $datetime2);
									$daysStart = $interval->format('%a');
								}
								else
								{
									$daysStart = 0;
								}
								$downDate = $t_nominaemployee->workerData->first()->downDate != '' && new \DateTime($t_nominaemployee->workerData->first()->downDate) > new \DateTime($calculations['admissionDate']) ? $t_nominaemployee->workerData->first()->downDate : null;
								$daysDown = 0;
								if ($downDate !='' && new \DateTime($downDate) >= new \DateTime($request->from_date[$i]) && new \DateTime($downDate) <= new \DateTime($request->to_date[$i])) 
								{
									$date1    = new \DateTime($downDate);
									$date2    = new \DateTime($request->to_date[$i]);
									$diff     = $date1->diff($date2);
									$daysDown = $diff->days;
								}
								else
								{
									$daysDown = 0;
								}
								$calculations['workedDays']  = (App\CatPeriodicity::find($request->periodicity[$i])->days)-$request->absence[$i]-$daysStart-$daysDown;
								$calculations['periodicity'] = App\CatPeriodicity::find($request->periodicity[$i])->description;
								$calculations['rangeDate']   = $request->from_date[$i].' '.$request->to_date[$i];
								switch ($request->periodicity[$i]) 
								{
									case '02':
										$d = new DateTime($request->from_date[$i]);
										$d->modify('next thursday');
										$calculations['divisorDayFormImss'] = App\Http\Controllers\AdministracionNominaController::days_count($d->format('m'),$d->format('Y'),4);
										break;
									case '04':
										$calculations['divisorDayFormImss'] = 2;
										break;
									case '05':
										$calculations['divisorDayFormImss'] = 1;
										break;
								}
								$d = new DateTime($request->from_date[$i]);
								$d->modify('next thursday');
								$calculations['daysMonth'] = App\Http\Controllers\AdministracionNominaController::days_month($d->format('m'),$d->format('Y'));
								if ($t_nominaemployee->workerData->first()->regime_id == '09')
								{
									$calculations['daysForImss'] = 0;
								}
								else
								{
									switch ($request->periodicity[$i]) 
									{
										case '02':
											if ($calculations['workedDays']<7) 
											{
												$calculations['daysForImss']	= $calculations['workedDays'];
											}
											else
											{
												$calculations['daysForImss']	= $calculations['daysMonth']/$calculations['divisorDayFormImss'];
											}
											break;

										case '04':
											if ($calculations['workedDays']<15) 
											{
												$calculations['daysForImss']	= $calculations['workedDays'];
											}
											else
											{
												$calculations['daysForImss']	= $calculations['daysMonth']/$calculations['divisorDayFormImss'];
											}
											break;

										case '05':
											if ($calculations['workedDays']<30) 
											{
												$calculations['daysForImss']	= $calculations['workedDays'];
											}
											else
											{
												$calculations['daysForImss']	= $calculations['daysMonth']/$calculations['divisorDayFormImss'];
											}
											break;
									}
								}
								//TIEMPO EXTRA Y DÍAS FESTIVOS
								$calculations['uma']           = App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
								$calculations['extra_time']    = $t_nominaemployee->extra_hours < 9 ? (($calculations['sd'] / 8) * 2 * $t_nominaemployee->extra_hours) : (($calculations['sd'] / 8 * 2 * 9) + ($calculations['sd'] / 8 * 3 * ($t_nominaemployee->extra_hours - 9)));
								$calculations['holiday']       = $t_nominaemployee->holidays * $calculations['sd'] * 2;
								$calculations['sunday_bonus']  = $calculations['sd'] * .25 * $t_nominaemployee->sundays;
								$calculations['sunday_except'] = $calculations['sunday_bonus'] > $calculations['uma'] ? $calculations['uma'] : $calculations['sunday_bonus'];
								$calculations['sunday_taxed']  = $calculations['sunday_bonus'] > $calculations['uma'] ? ($calculations['sunday_bonus'] - $calculations['sunday_except']) : 0;
								
								//PERCEPCIONES
								$calculations['salary']         = $calculations['sd']*$calculations['workedDays'];
								$calculations['loanPerception'] = $request->loan_perception[$i];
								$calculations['puntuality']     = $calculations['salary'] * (($t_nominaemployee->workerData->first()->bono/100)/2);
								$calculations['assistance']     = $calculations['salary'] * (($t_nominaemployee->workerData->first()->bono/100)/2);
								$parameterMinimumSalary         = App\Parameter::where('parameter_name','SM')->first()->parameter_value;
								$calculations['extra_hours']    = $calculations['sd'] == $parameterMinimumSalary ? 0 : ($calculations['extra_time'] > (5 * $calculations['uma']) ? ($calculations['extra_time'] - ($calculations['uma'] * 5)) : ($t_nominaemployee->extra_hours > 9 ? (($t_nominaemployee->extra_hours - 9) * $calculations['sd'] / 8 * 3) : ($calculations['extra_time'] * .5)));
								$calculations['holidays']       = $calculations['sd'] == $parameterMinimumSalary ? 0 : ($calculations['holiday'] > (5 * $calculations['uma']) ? ($calculations['holiday'] - ($calculations['uma'] * 5)) : ($calculations['holiday'] * .5));
								//return $calculations;
								//calculo para el subsidio
								$calculations['baseTotalDePercepciones'] = $calculations['salary'] + $calculations['puntuality'] + $calculations['assistance'] + $calculations['extra_hours'] + $calculations['holidays'] + $calculations['sunday_taxed'];
								$calculations['baseISR']                 = ($calculations['baseTotalDePercepciones']/$calculations['workedDays'])*30.4;
								$parameterISR                            = App\ParameterISR::where('inferior','<=',$calculations['baseISR'])->where('lapse',30)->get();
								$calculations['limiteInferior']          = $parameterISR->last()->inferior;
								$calculations['excedente']               = $calculations['baseISR']-$calculations['limiteInferior'];
								$calculations['factor']                  = $parameterISR->last()->excess/100;
								$calculations['isrMarginal']             = $calculations['excedente'] * $calculations['factor'];
								$calculations['cuotaFija']               = $parameterISR->last()->quota;
								$calculations['isrAntesDelSubsidio']     = (($calculations['isrMarginal'] + $calculations['cuotaFija'])/30.4)*$calculations['workedDays'];
								$parameterSubsidy                        = App\ParameterSubsidy::where('inferior','<=',$calculations['baseISR'])->where('lapse',30)->get();
								if($calculations['baseISR'] <= 7382.34)
								{
									$calculations['subsidioAlEmpleo'] = ($parameterSubsidy->last()->subsidy/30.4)*$calculations['workedDays'];
								}
								else
								{
									$calculations['subsidioAlEmpleo'] = 0;
								}
								if (($calculations['isrAntesDelSubsidio'] - $calculations['subsidioAlEmpleo']) > 0) 
								{
									$calculations['isrARetener'] = $calculations['isrAntesDelSubsidio'] - $calculations['subsidioAlEmpleo'];
									$calculations['subsidio']    = 0; 	
								}
								else
								{
									$calculations['isrARetener'] = 0;
									$calculations['subsidio']    = round(($calculations['isrAntesDelSubsidio'] - $calculations['subsidioAlEmpleo'])*(-1),2); 	
								}
								$calculations['totalPerceptions']	= round(round($calculations['salary'],2) + round($calculations['loanPerception'],2) + round($calculations['puntuality'],2) + round($calculations['assistance'],2) + round($calculations['subsidio'],2) + round($calculations['extra_time'],2) + round($calculations['holiday'],2) + round($calculations['sunday_bonus'],2),2);
								//----------------------------

								//RETENCIONES

								// calculo de IMSS (cuotas obrero-patronal)
								$calculations['SalarioBaseDeCotizacion'] = $calculations['sdi'];
								$calculations['diasDelPeriodoMensual']   = $calculations['daysForImss'];
								$calculations['diasDelPeriodoBimestral'] = $calculations['daysForImss'];
								$calculations['primaDeRiesgoDeTrabajo']  = App\EmployerRegister::where('employer_register',$t_nominaemployee->workerData->first()->employer_register)->first()->risk_number; 
								
								if (($calculations['uma']*3) > $calculations['SalarioBaseDeCotizacion'])
								{
									$calculations['imssExcedente'] = 0;
								}
								else
								{
									$calculations['imssExcedente'] = ((($calculations['SalarioBaseDeCotizacion']-(3*$calculations['uma']))*$calculations['diasDelPeriodoMensual'])*0.4)/100;
								}
								$calculations['prestacionesEnDinero']     = (($calculations['SalarioBaseDeCotizacion']*$calculations['diasDelPeriodoMensual'])*0.25)/100;
								$calculations['gastosMedicosPensionados'] = (($calculations['SalarioBaseDeCotizacion']*$calculations['diasDelPeriodoMensual'])*0.375)/100;
								$calculations['invalidezVidaPatronal']    = (($calculations['SalarioBaseDeCotizacion']*$calculations['diasDelPeriodoMensual'])*0.625)/100;
								$calculations['cesantiaVejez']            = (($calculations['SalarioBaseDeCotizacion']*$calculations['diasDelPeriodoMensual'])*1.125)/100;
								$calculations['imss']                     = $calculations['imssExcedente']+$calculations['prestacionesEnDinero']+$calculations['gastosMedicosPensionados']+$calculations['invalidezVidaPatronal']+$calculations['cesantiaVejez'];

								//calculo infonavit
								$calculations['diasBimestre']    = App\Http\Controllers\AdministracionNominaController::days_bimester($request->from_date[$i]);
								$calculations['factorInfonavit'] = App\Parameter::where('parameter_name','INFONAVIT_FACTOR')->first()->parameter_value;
								if ($t_nominaemployee->workerData->first()->infonavitDiscountType != '') 
								{
									$calculations['descuentoEmpleado'] = $t_nominaemployee->workerData->first()->infonavitDiscount;
									$calculations['quinceBimestral']   = App\Http\Controllers\AdministracionNominaController::pay_infonavit($request->from_date[$i],$request->to_date[$i]);
									switch ($t_nominaemployee->workerData->first()->infonavitDiscountType) 
									{
										case 1:
											$calculations['descuentoInfonavitTemp'] = (($calculations['descuentoEmpleado']*$calculations['factorInfonavit']*2)/$calculations['diasBimestre'])*$calculations['daysForImss']+$calculations['quinceBimestral']; 
											break;

										case 2:
											$calculations['descuentoInfonavitTemp'] = $calculations['descuentoEmpleado']*2/$calculations['diasBimestre']*$calculations['daysForImss']+$calculations['quinceBimestral']; 
											break;

										case 3:
											$calculations['descuentoInfonavitTemp'] = (($calculations['sdi']*($calculations['descuentoEmpleado']/100)*$calculations['daysForImss']))+$calculations['quinceBimestral']; 
											break;
									}
								}
								else
								{
									$calculations['descuentoInfonavitTemp'] = 0 ;
								}
								// -------------------
								$calculations['fonacot']               = (($t_nominaemployee->workerData->first()->fonacot/30.4)*$calculations['daysForImss']);
								$calculations['loanRetention']         = $request->loan_retention[$i];
								$calculations['otherRetentionConcept'] = $request->other_retention_concept[$i];
								$calculations['otherRetentionAmount']  = $request->other_retention_amount[$i];
								$calculations['totalRetentionsTemp']   = round($calculations['imss'],2)+round($calculations['descuentoInfonavitTemp'],2)+round($calculations['fonacot'],2)+round($calculations['loanRetention'],2)+round($calculations['isrARetener'],2)+round($calculations['otherRetentionAmount'],2);
								$calculations['percentage']            = ($calculations['totalRetentionsTemp'] * 100) / $calculations['salary'];
								if($calculations['percentage'] > 80)
								{
									$calculations['descuentoInfonavit']            = 0 ;
									$calculations['descuentoInfonavitComplemento'] = $calculations['descuentoInfonavitTemp'];
								}
								else
								{
									$calculations['descuentoInfonavit']            = $calculations['descuentoInfonavitTemp'];
									$calculations['descuentoInfonavitComplemento'] = 0;
								}

								//pensión alimenticia

								if ($t_nominaemployee->workerData->first()->alimonyDiscountType != '') 
								{
									$calculations['totalRetentionsTemp']	= round(round($calculations['imss'],2)+round($calculations['descuentoInfonavit'],2)+round($calculations['fonacot'],2)+round($calculations['loanRetention'],2)+round($calculations['isrARetener'],2)+round($calculations['otherRetentionAmount'],2),2);
								
									$calculations['netIncomeTemp']			= round($calculations['totalPerceptions']-$calculations['totalRetentionsTemp'],2);

									switch ($t_nominaemployee->workerData->first()->alimonyDiscountType) 
									{
										case 1: //monto
											$calculations['amountAlimony']	= $t_nominaemployee->workerData->first()->alimonyDiscount;
											$calculations['alimony']		= $calculations['amountAlimony'];
											break;

										case 2: // porcentaje
											$calculations['amountAlimony']	= $t_nominaemployee->workerData->first()->alimonyDiscount;
											$calculations['alimony']		= ($calculations['netIncomeTemp']*$calculations['amountAlimony'])/100;
											break;
										default:
											# code...
											break;
									}

									$calculations['totalRetentions']	= round(round($calculations['imss'],2)+round($calculations['descuentoInfonavit'],2)+round($calculations['fonacot'],2)+round($calculations['loanRetention'],2)+round($calculations['isrARetener'],2)+round($calculations['alimony'],2)+round($calculations['otherRetentionAmount'],2),2);
									$calculations['netIncome']			= round($calculations['totalPerceptions']-$calculations['totalRetentions'],2);
								}
								else
								{ 
									$calculations['alimony']			= 0;
									$calculations['totalRetentions']	= round(round($calculations['imss'],2)+round($calculations['descuentoInfonavit'],2)+round($calculations['fonacot'],2)+round($calculations['loanRetention'],2)+round($calculations['isrARetener'],2)+round($calculations['alimony'],2)+round($calculations['otherRetentionAmount'],2),2);
									$calculations['netIncome']			= round($calculations['totalPerceptions']-$calculations['totalRetentions'],2);
								}

								//return $calculations;
								// cambio firstOrNew
								$t_salary                          = App\Salary::firstOrNew(['idnominaEmployee' => $t_nominaemployee->idnominaEmployee]);
								$t_salary->idnominaEmployee        = $t_nominaemployee->idnominaEmployee;
								$t_salary->sd                      = $calculations['sd'];
								$t_salary->sdi                     = $calculations['sdi'];
								$t_salary->workedDays              = $calculations['workedDays'];
								$t_salary->daysForImss             = $calculations['daysForImss'];
								$t_salary->salary                  = $calculations['salary'];
								$t_salary->loan_perception         = $calculations['loanPerception'];
								$t_salary->puntuality              = $calculations['puntuality'];
								$t_salary->assistance              = $calculations['assistance'];
								$t_salary->extra_hours             = $t_nominaemployee->extra_hours;
								$t_salary->extra_time              = $calculations['extra_time'];
								$t_salary->extra_time_taxed        = $calculations['extra_hours'];
								$t_salary->holidays                = $t_nominaemployee->holidays;
								$t_salary->holiday                 = $calculations['holiday'];
								$t_salary->holiday_taxed           = $calculations['holidays'];
								$t_salary->sundays                 = $t_nominaemployee->sundays;
								$t_salary->exempt_sunday           = $calculations['sunday_except'];
								$t_salary->taxed_sunday            = $calculations['sunday_taxed'];
								$t_salary->subsidy                 = $calculations['subsidio'];
								$t_salary->totalPerceptions        = $calculations['totalPerceptions'];
								$t_salary->imss                    = $calculations['imss'];
								$t_salary->infonavit               = $calculations['descuentoInfonavit'];
								$t_salary->infonavitComplement     = $calculations['descuentoInfonavitComplemento'];
								$t_salary->fonacot                 = $calculations['fonacot'];
								$t_salary->loan_retention          = $calculations['loanRetention'];
								$t_salary->other_retention_amount  = $calculations['otherRetentionAmount'];
								$t_salary->other_retention_concept = $calculations['otherRetentionConcept'];
								$t_salary->isrRetentions           = $calculations['isrARetener'];
								$t_salary->alimony                 = $calculations['alimony'];
								$t_salary->totalRetentions         = $calculations['totalRetentions'];
								$t_salary->netIncome               = $calculations['netIncome'];
								$t_salary->risk_number             = $calculations['primaDeRiesgoDeTrabajo'];
								$t_salary->uma                     = $calculations['uma'];
								$t_salary->idAccountBeneficiary    = $request->idAccountBeneficiary[$i];
								if ($request->paymentWay[$i] == 1) 
								{
									if ($request->idemployeeAccount[$i] == '' && $t_nominaemployee->employee->first()->bankData()->where('visible',1)->exists()) 
									{
										$t_salary->idpaymentMethod		= $request->paymentWay[$i];
										$t_salary->idemployeeAccounts	= $t_nominaemployee->employee->first()->bankData->where('visible',1)->first()->id;
									}
									elseif ($request->idemployeeAccount[$i] != '')
									{
										$t_salary->idpaymentMethod		= $request->paymentWay[$i];
										$t_salary->idemployeeAccounts	= $request->idemployeeAccount[$i];
									}
									else 
									{
										$t_salary->idpaymentMethod		= 2;
										$t_salary->idemployeeAccounts	= null;
									}
								}
								else
								{
									$t_salary->idpaymentMethod    = $request->paymentWay[$i];
									$t_salary->idemployeeAccounts = null;
								}
								$t_salary->subsidyCaused           = $calculations['subsidioAlEmpleo'];
								$t_salary->save();
								$t_nominaemployeeaccount           = new App\NominaEmployeeAccounts();
								$t_nominaemployeeaccount->idSalary = $t_salary->idSalary;
								if ($request->paymentWay[$i] == 1) 
								{
									if ($request->idemployeeAccount[$i] == '' && $t_nominaemployee->employee->first()->bankData()->where('visible',1)->exists())
									{
										$t_nominaemployeeaccount->idemployeeAccounts = $t_nominaemployee->employee->first()->bankData->where('visible',1)->first()->id;
									}
									else
									{
										$t_nominaemployeeaccount->idemployeeAccounts	= $request->idemployeeAccount[$i];
									}
								}
								else
								{
									$t_nominaemployeeaccount->idemployeeAccounts	= null;
								}
								$t_nominaemployeeaccount->save();

								$totalRequest	+= round($calculations['netIncome']+$calculations['alimony'],2);
								$calculations	= [];
							}
							$t_nomina					= App\Nomina::find($t_request->nominasReal->first()->idnomina);
							$t_nomina->amount			= $totalRequest;
							$t_nomina->save();
							break;

						case '002':
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{  
								// sueldo diario = sueldo neto/periodicidad
								// dias trabajados - se usa fecha de alta
								// todos los aguinaldo se calculan del 1 de enero al 12 de diciembre
								// sino se calcula del 13 al 31 de diciembre
								// dias para aguinaldo: 15 dias * dias trabajados / 365
								// no fiscal = dias para aguinaldo * sueldo diario
								$t_nominaemployee					= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$t_nominaemployee->day_bonus		= $request->day_bonus[$i];
								$t_nominaemployee->idCatPeriodicity	= $request->periodicity[$i];
								$t_nominaemployee->total			= $request->netIncome[$i];
								$t_nominaemployee->save();

								$calculationsNetIncome = [];
								//calculo para dias de vacaciones
								$calculationsNetIncome['fechaIngreso']		= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$calculationsNetIncome['fechaActual']		= Carbon::now();
								$calculationsNetIncome['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculationsNetIncome['fechaIngreso'],$calculationsNetIncome['fechaActual']);
								$calculationsNetIncome['yearsWork']			= ceil($calculationsNetIncome['diasTrabajados']/$daysYear);
								if ($calculationsNetIncome['yearsWork'] > 24) 
								{
									$calculationsNetIncome['vacationDays']	= 20;
								}
								else
								{
									$calculationsNetIncome['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculationsNetIncome['yearsWork'])->where('toYear','>=',$calculationsNetIncome['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculationsNetIncome['yearsWork'])->where('toYear','>=',$calculationsNetIncome['yearsWork'])->first()->days : 0;
								}

								//-------------------

								$calculationsNetIncome['prima_vac_esp']					= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;

								switch ($request->periodicity[$i]) 
								{
									case '02':
										$calculationsNetIncome['divisor'] = 7;
										break;

									case '04':
										$calculationsNetIncome['divisor'] = 15;
										break;

									case '05':
										$d = new DateTime(Carbon::now());
										$calculationsNetIncome['divisor'] = App\Http\Controllers\AdministracionNominaController::days_month($d->format('m'),$d->format('Y'));
										break;
									
									default:
										break;
								}

								$calculationsNetIncome['calc_sdi']	 						= $request->netIncome[$i]/$calculationsNetIncome['divisor'];

								$calculationsNetIncome['sdi']								= $calculationsNetIncome['calc_sdi'];
								$calculationsNetIncome['sd']								= round($calculationsNetIncome['sdi']/((($calculationsNetIncome['vacationDays']*$calculationsNetIncome['prima_vac_esp'])+15+$daysYear)/$daysYear),2);
								$calculationsNetIncome['uma']								= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
								$calculationsNetIncome['exento']							= $calculationsNetIncome['uma']*30; 
								$calculationsNetIncome['diasParaAguinaldo']					= $request->day_bonus[$i];
								$calculationsNetIncome['parteProporcionalParaAguinaldo']	= round((15*$calculationsNetIncome['diasParaAguinaldo'])/$daysYear,6);


								// PERCEPCIONES AGUINALDO---------------------------------------------------------------------

								if (($calculationsNetIncome['parteProporcionalParaAguinaldo'] * $calculationsNetIncome['sd']) < $calculationsNetIncome['exento']) 
								{
									$calculationsNetIncome['aguinaldoExento'] = $calculationsNetIncome['parteProporcionalParaAguinaldo'] * $calculationsNetIncome['sd'];
								}
								else
								{
									$calculationsNetIncome['aguinaldoExento'] = $calculationsNetIncome['exento'];
								}

								if (($calculationsNetIncome['parteProporcionalParaAguinaldo'] * $calculationsNetIncome['sd']) > $calculationsNetIncome['exento']) 
								{
									$calculationsNetIncome['aguinaldoGravable'] = ($calculationsNetIncome['parteProporcionalParaAguinaldo'] * $calculationsNetIncome['sd'])-$calculationsNetIncome['aguinaldoExento'];
								}
								else
								{
									$calculationsNetIncome['aguinaldoGravable'] = 0;
								}

								$calculationsNetIncome['totalPercepciones'] = round($calculationsNetIncome['aguinaldoExento'],2) + round($calculationsNetIncome['aguinaldoGravable'],2);

								// --------------------------------------------------------------------------------------------

								
								$calculationsNetIncome['netIncome']			= $calculationsNetIncome['totalPercepciones'];

								$calculations = [];
								//calculo para dias de vacaciones
								$calculations['fechaIngreso']	= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$calculations['fechaActual']	= Carbon::now();
								$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
								$calculations['yearsWork']		= ceil($calculations['diasTrabajados']/$daysYear);
								if ($calculations['yearsWork'] > 24) 
								{
									$calculations['vacationDays']	= 20;
								}
								else
								{
									$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
								}

								//-------------------

								$calculations['prima_vac_esp']					= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
								$calculations['sdi']							= $t_nominaemployee->workerData->first()->sdi;
								$calculations['sd']								= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+$daysYear)/$daysYear),2);
								$calculations['uma']							= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
								$calculations['exento']							= $calculations['uma']*30; 
								$calculations['diasParaAguinaldo']				= $request->day_bonus[$i];
								$calculations['parteProporcionalParaAguinaldo']	= round((15*$calculations['diasParaAguinaldo'])/$daysYear,6);


								// PERCEPCIONES AGUINALDO---------------------------------------------------------------------

								if (($calculations['parteProporcionalParaAguinaldo'] * $calculations['sd']) < $calculations['exento']) 
								{
									$calculations['aguinaldoExento'] = $calculations['parteProporcionalParaAguinaldo'] * $calculations['sd'];
								}
								else
								{
									$calculations['aguinaldoExento'] = $calculations['exento'];
								}

								if (($calculations['parteProporcionalParaAguinaldo'] * $calculations['sd']) > $calculations['exento']) 
								{
									$calculations['aguinaldoGravable'] = ($calculations['parteProporcionalParaAguinaldo'] * $calculations['sd'])-$calculations['aguinaldoExento'];
								}
								else
								{
									$calculations['aguinaldoGravable'] = 0;
								}

								$calculations['totalPercepciones'] = round(round($calculations['aguinaldoExento'],2) + round($calculations['aguinaldoGravable'],2),2);

								// --------------------------------------------------------------------------------------------

								// RETENCIONES- ISR ---------------------------------------------------------------------

								// ISR 1ER FRACCION

								$calculations['baseISR_fraccion1']			= round((($calculations['aguinaldoGravable']/$daysYear)*30.4)+($calculations['sd']*30),6);
								$parameterISRF1								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion1'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion1']	= $parameterISRF1->last()->inferior;
								$calculations['excedente_fraccion1']		= round($calculations['baseISR_fraccion1']-$calculations['limiteInferior_fraccion1'],6);
								$calculations['factor_fraccion1']			= round($parameterISRF1->last()->excess/100,6);
								$calculations['isrMarginal_fraccion1']		= round($calculations['excedente_fraccion1'] * $calculations['factor_fraccion1'],6);
								$calculations['cuotaFija_fraccion1']		= round($parameterISRF1->last()->quota,6);
								$calculations['isr_fraccion1']				= round($calculations['isrMarginal_fraccion1']+$calculations['cuotaFija_fraccion1'],6);

								// ISR 2DA FRACCION

								$calculations['baseISR_fraccion2']			= round($calculations['sd']*30,6);
								$parameterISRF2								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion2'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion2']	= $parameterISRF2->last()->inferior;
								$calculations['excedente_fraccion2']		= round($calculations['baseISR_fraccion2']-$calculations['limiteInferior_fraccion2'],6);
								$calculations['factor_fraccion2']			= round($parameterISRF2->last()->excess/100,6);
								$calculations['isrMarginal_fraccion2']		= round($calculations['excedente_fraccion2'] * $calculations['factor_fraccion2'],6);
								$calculations['cuotaFija_fraccion2']		= round($parameterISRF2->last()->quota,6);
								$calculations['isr_fraccion2']				= round($calculations['isrMarginal_fraccion2']+$calculations['cuotaFija_fraccion2'],6);

								$calculations['resta']		= round($calculations['isr_fraccion1']-$calculations['isr_fraccion2'],6);
								$calculations['factor1']	= round((($calculations['aguinaldoGravable']/$daysYear) * 30.4),6);
								if($calculations['factor1'] == 0)
								{
									$calculations['factor2']	= 0;
								}
								else
								{
									$calculations['factor2']	= round($calculations['resta']/$calculations['factor1'],6);
								}
								$calculations['isr']		= round($calculations['factor2']*$calculations['aguinaldoGravable'],6);

								//pensión alimenticia

								if ($t_nominaemployee->workerData->first()->alimonyDiscountType != '') 
								{
									$calculations['totalRetencionesTemp'] = round($calculations['isr'],2);
								
									$calculations['netIncomeTemp']			= round($calculations['totalPercepciones']-$calculations['totalRetencionesTemp'],2);

									switch ($t_nominaemployee->workerData->first()->alimonyDiscountType) 
									{
										case 1: //monto
											$calculations['amountAlimony']	= $t_nominaemployee->workerData->first()->alimonyDiscount;
											$calculations['alimony']		= $calculations['amountAlimony'];
											break;

										case 2: // porcentaje
											$calculations['amountAlimony']	= $t_nominaemployee->workerData->first()->alimonyDiscount;
											$calculations['alimony']		= ($calculations['netIncomeTemp']*$calculations['amountAlimony'])/100;
											break;
										default:
											# code...
											break;
									}

									$calculations['totalRetenciones']	= round(round($calculations['isr'],2)+round($calculations['alimony'],2),2);
									$calculations['netIncome']			= round($calculations['totalPercepciones']-$calculations['totalRetenciones'],2);
								}
								else
								{ 
									$calculations['alimony']			= 0;
									$calculations['totalRetenciones'] = round($calculations['isr'],2);
								
									$calculations['netIncome']			= round($calculations['totalPercepciones']-$calculations['totalRetenciones'],2);
								}

								// --------------------------------------------------------------------------------------------


								$initYear	= date('Y').'-01-01'; 
								$endYear	= date('Y').'-12-31'; 

								$calculations['sueldoDiarioNF'] = $request->netIncome[$i]/$calculationsNetIncome['divisor'];
								$calculations['fechaIngresoNF']		= $t_nominaemployee->workerData->first()->admissionDate->format('Y-m-d');

								if (new \DateTime($calculations['fechaIngresoNF']) < new \DateTime($initYear))
								{
									$calculations['diasTrabajadosNF'] = $daysYear;
								}
								else
								{
									$datetime2	= date_create($endYear);
									$datetime1	= date_create($calculations['fechaIngresoNF']);
									$interval	= date_diff($datetime1, $datetime2);

									$daysDiff = $interval->format('%a');
									$calculations['diasTrabajadosNF'] = $daysDiff+1;
								}

								$calculations['diasParaAguinaldoNF'] = 15 * ($calculations['diasTrabajadosNF']/$daysYear);

								$calculations['sueldoNF'] = round($calculations['diasParaAguinaldoNF'] * $calculations['sueldoDiarioNF'],2);
								// cambio firstOrNew
								$t_bonus									= App\Bonus::firstOrNew(['idnominaEmployee' => $t_nominaemployee->idnominaEmployee]);
								$t_bonus->idnominaEmployee					= $t_nominaemployee->idnominaEmployee;
								$t_bonus->sd								= $calculations['sd'];
								$t_bonus->sdi								= $calculations['sdi'];
								$t_bonus->dateOfAdmission					= $calculations['fechaIngreso'];
								$t_bonus->daysForBonuses					= $calculations['diasParaAguinaldo'];
								$t_bonus->proportionalPartForChristmasBonus	= $calculations['parteProporcionalParaAguinaldo'];
								$t_bonus->exemptBonus						= $calculations['aguinaldoExento'];
								$t_bonus->taxableBonus						= $calculations['aguinaldoGravable'];
								$t_bonus->totalPerceptions					= $calculations['totalPercepciones'];
								$t_bonus->isr								= $calculations['isr'];
								$t_bonus->totalTaxes						= $calculations['totalRetenciones'];
								$t_bonus->netIncome							= $calculations['netIncome'];
								$t_bonus->totalIncomeBonus					= $calculations['sueldoNF'];
								$t_bonus->alimony							= $calculations['alimony'];
								$t_bonus->idAccountBeneficiary				= $request->idAccountBeneficiary[$i];

								if ($request->paymentWay[$i] == 1) 
								{
									if ($request->idemployeeAccount[$i] == '' && $t_nominaemployee->employee->first()->bankData()->where('visible',1)->exists()) 
									{
										$t_bonus->idpaymentMethod		= $request->paymentWay[$i];
										$t_bonus->idemployeeAccounts	= $t_nominaemployee->employee->first()->bankData->where('visible',1)->first()->id;
									}
									elseif ($request->idemployeeAccount[$i] != '')
									{
										$t_bonus->idpaymentMethod		= $request->paymentWay[$i];
										$t_bonus->idemployeeAccounts	= $request->idemployeeAccount[$i];
									}
									else 
									{
										$t_bonus->idpaymentMethod		= 2;
										$t_bonus->idemployeeAccounts	= null;
									}
								}
								else
								{
									$t_bonus->idpaymentMethod		= $request->paymentWay[$i];
									$t_bonus->idemployeeAccounts	= null;
								}
								$t_bonus->save();

								$t_nominaemployeeaccount						= new App\NominaEmployeeAccounts();
								$t_nominaemployeeaccount->idBonus				= $t_bonus->idBonus;
								if ($request->paymentWay[$i] == 1) 
								{
									if ($request->idemployeeAccount[$i] == '' && $t_nominaemployee->employee->first()->bankData()->where('visible',1)->exists())
									{
										$t_nominaemployeeaccount->idemployeeAccounts = $t_nominaemployee->employee->first()->bankData->where('visible',1)->first()->id;
									}
									else
									{
										$t_nominaemployeeaccount->idemployeeAccounts	= $request->idemployeeAccount[$i];
									}
								}
								else
								{
									$t_nominaemployeeaccount->idemployeeAccounts	= null;
								}
								$t_nominaemployeeaccount->save();

								$totalRequest			+= $calculations['netIncome']+$calculations['alimony'];
								$calculationsNetIncome	= [];
								$calculations			= [];

							}
							
							$t_nomina					= App\Nomina::find($t_request->nominasReal->first()->idnomina);
							$t_nomina->amount			= round($totalRequest,2);
							$t_nomina->save();
							break;

						case '003':
						case '004':
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee					= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$t_nominaemployee->down_date		= $request->down_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->down_date[$i])->format('Y-m-d') : null;
								$t_nominaemployee->worked_days		= $request->worked_days[$i];
								$t_nominaemployee->other_perception	= $request->other_perception[$i];
								$t_nominaemployee->other_retention	= $request->other_retention[$i];
								$t_nominaemployee->idCatPeriodicity	= $request->periodicity[$i];
								$t_nominaemployee->total			= $request->netIncome[$i];
								$t_nominaemployee->save();

								$workerData						= App\WorkerData::find($t_nominaemployee->idworkingData);
								
								$workerDataNew					= $workerData->replicate();
								$workerDataNew->downDate		= $request->down_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->down_date[$i])->format('Y-m-d') : null;
								$workerDataNew->visible			= 1;
								$workerDataNew->workerStatus	= 1;
								$workerDataNew->push();

								$workerData->visible = 0;
								$workerData->save();

								$calculationsNetIncome                   = [];

								$calculationsNetIncome['fechaIngreso']	= $t_nominaemployee->workerData->first()->admissionDateOld->format('Y-m-d');
								$calculationsNetIncome['fechaBaja']		= $request->down_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->down_date[$i])->format('Y-m-d') : null;
								
								$calculationsNetIncome['fechaActual']		= Carbon::now();
								$calculationsNetIncome['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculationsNetIncome['fechaIngreso'],$calculationsNetIncome['fechaActual']);
								$calculationsNetIncome['añosTrabajados']	= ceil($calculationsNetIncome['diasTrabajados']/365);

								$calculationsNetIncome['diasTrabajadosParaAñosCompletos'] = App\Http\Controllers\AdministracionNominaController::daysPassed($calculationsNetIncome['fechaIngreso'],$calculationsNetIncome['fechaBaja']);

								$calculationsNetIncome['añosCompletos']	= floor($calculationsNetIncome['diasTrabajadosParaAñosCompletos']/365);
								if ($calculationsNetIncome['añosTrabajados'] > 24) 
								{
									$calculationsNetIncome['diasDeVacaciones']	= 20;
								}
								else
								{
									$calculationsNetIncome['diasDeVacaciones']	= App\ParameterVacation::where('fromYear','<=',$calculationsNetIncome['añosTrabajados'])->where('toYear','>=',$calculationsNetIncome['añosTrabajados'])->first()->days;
								}

								//------------------------------------------------------------------
								
								$calculationsNetIncome['prima_vac_esp']		= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;

								switch ($request->periodicity[$i]) 
								{
									case '02':
										$calculationsNetIncome['divisor'] = 7;
										break;

									case '04':
										$calculationsNetIncome['divisor'] = 15;
										break;

									case '05':
										$d = new DateTime(Carbon::now());
										$calculationsNetIncome['divisor'] = App\Http\Controllers\AdministracionNominaController::days_month($d->format('m'),$d->format('Y'));
										break;
									
									default:
										break;
								}

								$calculationsNetIncome['calc_sdi']				= $request->netIncome[$i]/$calculationsNetIncome['divisor'];
								
								$calculationsNetIncome['sdi']					= $calculationsNetIncome['calc_sdi'];
								$calculationsNetIncome['sd']					= round($calculationsNetIncome['sdi']/((($calculationsNetIncome['diasDeVacaciones']*$calculationsNetIncome['prima_vac_esp'])+15+365)/365),2);
								
								$calculationsNetIncome['diasTrabajadosM']		= $request->worked_days[$i];
								
								$calculationsNetIncome['diasParaVacaciones']	= ($calculationsNetIncome['diasDeVacaciones']*$calculationsNetIncome['diasTrabajadosM'])/365;
								//dias trabajados para aguinaldo va del 1 de enero a la fecha de baja
								$date1 = new \DateTime(date("Y").'-01-01');
								$date2 = new \DateTime($calculationsNetIncome['fechaIngreso']);
								if ($date2 > $date1) 
								{
									$fechaParaDiasAguinaldo = $calculationsNetIncome['fechaIngreso'];
								}
								else
								{
									$fechaParaDiasAguinaldo = date("Y").'-01-01';
								}
								$calculationsNetIncome['diasTrabajadosParaAguinaldo'] = App\Http\Controllers\AdministracionNominaController::daysPassed($fechaParaDiasAguinaldo,$calculationsNetIncome['fechaBaja'])+1;

								$calculationsNetIncome['diasParaAguinaldo'] 	= ($calculationsNetIncome['diasTrabajadosParaAguinaldo']*15)/365;

								if ($request->type_payroll == '004') 
								{
									$calculationsNetIncome['sueldoPorLiquidacion']		= round($calculationsNetIncome['sd']*90,6);
									$calculationsNetIncome['veinteDiasPorAñoServicio']	= round(20*$calculationsNetIncome['añosCompletos']*$calculationsNetIncome['sd'],6);
									
									// VARIABLES -------------------------------------------------------
									$calculationsNetIncome['uma']							= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
									$calculationsNetIncome['salarioMinimo']					= App\Parameter::where('parameter_name','SALARY_VDF')->first()->parameter_value; 
									$calculationsNetIncome['valorPrimaAntiguedad']			= $calculationsNetIncome['salarioMinimo']*2;
									$calculationsNetIncome['exento']							= $calculationsNetIncome['uma']*90; 
									$calculationsNetIncome['valorAguinaldoExento']			= $calculationsNetIncome['uma']*30; 
									$calculationsNetIncome['valorPrimaVacacaionalExenta']	= $calculationsNetIncome['uma']*15; 
									$calculationsNetIncome['valorIndemnizacionExenta']		= $calculationsNetIncome['uma']*90;
									//  PRIMA DE ANTIGUEDAD ------------------------------------------------------------------

									if ($calculationsNetIncome['sd']>=$calculationsNetIncome['valorPrimaAntiguedad']) 
									{
										$calculationsNetIncome['primaAntiguedad'] = round($calculationsNetIncome['añosCompletos']*12*$calculationsNetIncome['valorPrimaAntiguedad'],6);
									}
									else
									{
										$calculationsNetIncome['primaAntiguedad'] = round($calculationsNetIncome['añosCompletos']*12*$calculationsNetIncome['sd'],6);
									}

									//  INDEMNIZACION ------------------------------------------------------------------
									$calculationsNetIncome['indemnizacion'] =  round($calculationsNetIncome['sueldoPorLiquidacion']+$calculationsNetIncome['veinteDiasPorAñoServicio']+$calculationsNetIncome['primaAntiguedad'],6);

									if ($calculationsNetIncome['indemnizacion'] < $calculationsNetIncome['valorIndemnizacionExenta']) 
									{
										$calculationsNetIncome['indemnizacionExcenta']	= $calculationsNetIncome['indemnizacion'];
									}
									else
									{
										$calculationsNetIncome['indemnizacionExcenta']	= $calculationsNetIncome['valorIndemnizacionExenta'];
									}


									if ($calculationsNetIncome['indemnizacion'] > $calculationsNetIncome['valorIndemnizacionExenta']) 
									{
										$calculationsNetIncome['indemnizacionGravada']	= $calculationsNetIncome['indemnizacion']-$calculationsNetIncome['indemnizacionExcenta'];
									}
									else
									{
										$calculationsNetIncome['indemnizacionGravada']	= 0;
									}

									$calculationsNetIncome['vacaciones']				= $calculationsNetIncome['diasParaVacaciones']*$calculationsNetIncome['sd'];


									// PERCEPCIONES AGUINALDO---------------------------------------------------------------------

									if (($calculationsNetIncome['diasParaAguinaldo'] * $calculationsNetIncome['sd']) < $calculationsNetIncome['valorAguinaldoExento']) 
									{
										$calculationsNetIncome['aguinaldoExento'] = $calculationsNetIncome['diasParaAguinaldo'] * $calculationsNetIncome['sd'];
									}
									else
									{
										$calculationsNetIncome['aguinaldoExento'] = $calculationsNetIncome['valorAguinaldoExento'];
									}

									if (($calculationsNetIncome['diasParaAguinaldo'] * $calculationsNetIncome['sd']) > $calculationsNetIncome['valorAguinaldoExento']) 
									{
										$calculationsNetIncome['aguinaldoGravable'] = ($calculationsNetIncome['diasParaAguinaldo'] * $calculationsNetIncome['sd'])-$calculationsNetIncome['aguinaldoExento'];
									}
									else
									{
										$calculationsNetIncome['aguinaldoGravable'] = 0;
									}


									//-------- PERCEPCIONES ---------------------------------------------------------------


									if (($calculationsNetIncome['vacaciones']*$calculationsNetIncome['prima_vac_esp'])<$calculationsNetIncome['valorPrimaVacacaionalExenta'])
									{
										$calculationsNetIncome['primaVacacionalExenta'] = round($calculationsNetIncome['vacaciones']*$calculationsNetIncome['prima_vac_esp'],6);
									}
									else
									{
										$calculationsNetIncome['primaVacacionalExenta'] = $calculationsNetIncome['valorPrimaVacacaionalExenta'];
									}

									if (($calculationsNetIncome['vacaciones']*$calculationsNetIncome['prima_vac_esp'])>$calculationsNetIncome['valorPrimaVacacaionalExenta'])
									{
										$calculationsNetIncome['primaVacacionalGravada'] = round(($calculationsNetIncome['vacaciones']*$calculationsNetIncome['prima_vac_esp'])-$calculationsNetIncome['primaVacacionalExenta'],6);
									}
									else
									{
										$calculationsNetIncome['primaVacacionalGravada'] = 0;
									}

									$calculationsNetIncome['otrasPercepciones'] = round($request->other_perception[$i],2);

									$calculationsNetIncome['totalPercepciones'] = round(round($calculationsNetIncome['sueldoPorLiquidacion'],2)+round($calculationsNetIncome['veinteDiasPorAñoServicio'],2)+round($calculationsNetIncome['primaAntiguedad'],2)+round($calculationsNetIncome['vacaciones'],2)+round($calculationsNetIncome['aguinaldoExento'],2)+round($calculationsNetIncome['aguinaldoGravable'],2)+round($calculationsNetIncome['primaVacacionalExenta'],2)+round($calculationsNetIncome['primaVacacionalGravada'],2)+round($calculationsNetIncome['otrasPercepciones'],2),2);
								}
								else
								{
									// VARIABLES -------------------------------------------------------
									$calculationsNetIncome['uma']							= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
									$calculationsNetIncome['salarioMinimo']					= App\Parameter::where('parameter_name','SALARY_VDF')->first()->parameter_value; 
									$calculationsNetIncome['valorPrimaAntiguedad']			= $calculationsNetIncome['salarioMinimo']*2;
									$calculationsNetIncome['exento']							= $calculationsNetIncome['uma']*90; 
									$calculationsNetIncome['valorAguinaldoExento']			= $calculationsNetIncome['uma']*30; 
									$calculationsNetIncome['valorPrimaVacacaionalExenta']	= $calculationsNetIncome['uma']*15; 
									$calculationsNetIncome['valorIndemnizacionExenta']		= $calculationsNetIncome['uma']*90;
									//  PRIMA DE ANTIGUEDAD ------------------------------------------------------------------

									if ($calculationsNetIncome['sd']>=$calculationsNetIncome['valorPrimaAntiguedad']) 
									{
										$calculationsNetIncome['primaAntiguedad'] = round($calculationsNetIncome['añosCompletos']*12*$calculationsNetIncome['valorPrimaAntiguedad'],6);
									}
									else
									{
										$calculationsNetIncome['primaAntiguedad'] = round($calculationsNetIncome['añosCompletos']*12*$calculationsNetIncome['sd'],6);
									}

									//  INDEMNIZACION  ------------------------------------------------------------------

									if ($calculationsNetIncome['primaAntiguedad'] < $calculationsNetIncome['valorIndemnizacionExenta']) 
									{
										$calculationsNetIncome['indemnizacionExcenta']	= $calculationsNetIncome['primaAntiguedad'];
									}
									else
									{
										$calculationsNetIncome['indemnizacionExcenta']	= $calculationsNetIncome['valorIndemnizacionExenta'];
									}


									if ($calculationsNetIncome['primaAntiguedad'] > $calculationsNetIncome['valorIndemnizacionExenta']) 
									{
										$calculationsNetIncome['indemnizacionGravada']	= $calculationsNetIncome['primaAntiguedad']-$calculationsNetIncome['indemnizacionExcenta'];
									}
									else
									{
										$calculationsNetIncome['indemnizacionGravada']	= 0;
									}

									$calculationsNetIncome['vacaciones']				= $calculationsNetIncome['diasParaVacaciones']*$calculationsNetIncome['sd'];


									// PERCEPCIONES AGUINALDO---------------------------------------------------------------------

									if (($calculationsNetIncome['diasParaAguinaldo'] * $calculationsNetIncome['sd']) < $calculationsNetIncome['valorAguinaldoExento']) 
									{
										$calculationsNetIncome['aguinaldoExento'] = $calculationsNetIncome['diasParaAguinaldo'] * $calculationsNetIncome['sd'];
									}
									else
									{
										$calculationsNetIncome['aguinaldoExento'] = $calculationsNetIncome['valorAguinaldoExento'];
									}

									if (($calculationsNetIncome['diasParaAguinaldo'] * $calculationsNetIncome['sd']) > $calculationsNetIncome['valorAguinaldoExento']) 
									{
										$calculationsNetIncome['aguinaldoGravable'] = ($calculationsNetIncome['diasParaAguinaldo'] * $calculationsNetIncome['sd'])-$calculationsNetIncome['aguinaldoExento'];
									}
									else
									{
										$calculationsNetIncome['aguinaldoGravable'] = 0;
									}


									//-------- PERCEPCIONES PRIMA VACACIONAL ---------------------------------------------------------------


									if (($calculationsNetIncome['vacaciones']*$calculationsNetIncome['prima_vac_esp'])<$calculationsNetIncome['valorPrimaVacacaionalExenta'])
									{
										$calculationsNetIncome['primaVacacionalExenta'] = round($calculationsNetIncome['vacaciones']*$calculationsNetIncome['prima_vac_esp'],6);
									}
									else
									{
										$calculationsNetIncome['primaVacacionalExenta'] = $calculationsNetIncome['valorPrimaVacacaionalExenta'];
									}

									if (($calculationsNetIncome['vacaciones']*$calculationsNetIncome['prima_vac_esp'])>$calculationsNetIncome['valorPrimaVacacaionalExenta'])
									{
										$calculationsNetIncome['primaVacacionalGravada'] = round(($calculationsNetIncome['vacaciones']*$calculationsNetIncome['prima_vac_esp'])-$calculationsNetIncome['primaVacacionalExenta'],6);
									}
									else
									{
										$calculationsNetIncome['primaVacacionalGravada'] = 0;
									}

									$calculationsNetIncome['otrasPercepciones'] = $request->other_perception[$i];

									$calculationsNetIncome['totalPercepciones'] = round($calculationsNetIncome['primaAntiguedad'],2)+round($calculationsNetIncome['vacaciones'],2)+round($calculationsNetIncome['aguinaldoExento'],2)+round($calculationsNetIncome['aguinaldoGravable'],2)+round($calculationsNetIncome['primaVacacionalExenta'],2)+round($calculationsNetIncome['primaVacacionalGravada'],2)+round($calculationsNetIncome['otrasPercepciones'],2);

									// ------------------------------------------------------------------------------------
								}

								$calculationsNetIncome['netIncome']			= $calculationsNetIncome['totalPercepciones'];

								// ----- calculo para dias de vacaciones ---------------------------
								$calculations                   = [];

								$calculations['fechaIngreso']	= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$calculations['fechaBaja']		= $request->down_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->down_date[$i])->format('Y-m-d') : null;
								
								$calculations['fechaActual']	= Carbon::now();
								$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
								$calculations['añosTrabajados']	= ceil($calculations['diasTrabajados']/365);

								$calculations['diasTrabajadosParaAñosCompletos'] = App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaBaja']);

								$calculations['añosCompletos']	= floor($calculations['diasTrabajadosParaAñosCompletos']/365);
								if ($calculations['añosTrabajados'] > 24) 
								{
									$calculations['diasDeVacaciones']	= 20;
								}
								else
								{
									$calculations['diasDeVacaciones']	= App\ParameterVacation::where('fromYear','<=',$calculations['añosTrabajados'])->where('toYear','>=',$calculations['añosTrabajados'])->first()->days;
								}

								//------------------------------------------------------------------
								
								$calculations['prima_vac_esp']		= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
								$calculations['sdi']				= $t_nominaemployee->workerData->first()->sdi;
								$calculations['sd']					= round($calculations['sdi']/((($calculations['diasDeVacaciones']*$calculations['prima_vac_esp'])+15+365)/365),2);
								
								$calculations['diasTrabajadosM']	= $request->worked_days[$i];
								
								$calculations['diasParaVacaciones']	= ($calculations['diasDeVacaciones']*$calculations['diasTrabajadosM'])/365;
								//dias trabajados para aguinaldo va del 1 de enero a la fecha de baja
								$date1 = new \DateTime(date("Y").'-01-01');
								$date2 = new \DateTime($calculations['fechaIngreso']);
								if ($date2 > $date1) 
								{
									$fechaParaDiasAguinaldo = $calculations['fechaIngreso'];
								}
								else
								{
									$fechaParaDiasAguinaldo = date("Y").'-01-01';
								}
								$calculations['diasTrabajadosParaAguinaldo'] = App\Http\Controllers\AdministracionNominaController::daysPassed($fechaParaDiasAguinaldo,$calculations['fechaBaja'])+1;

								$calculations['diasParaAguinaldo'] 	= ($calculations['diasTrabajadosParaAguinaldo']*15)/365;

								if ($request->type_payroll == '004') 
								{
									$calculations['sueldoPorLiquidacion']		= round($calculations['sd']*90,6);
									$calculations['veinteDiasPorAñoServicio']	= round(20*$calculations['añosCompletos']*$calculations['sd'],6);
									
									// VARIABLES -------------------------------------------------------
									$calculations['uma']							= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
									$calculations['salarioMinimo']					= App\Parameter::where('parameter_name','SALARY_VDF')->first()->parameter_value; 
									$calculations['valorPrimaAntiguedad']			= $calculations['salarioMinimo']*2;
									$calculations['exento']							= $calculations['uma']*90; 
									$calculations['valorAguinaldoExento']			= $calculations['uma']*30; 
									$calculations['valorPrimaVacacaionalExenta']	= $calculations['uma']*15; 
									$calculations['valorIndemnizacionExenta']		= $calculations['uma']*90;
									//  PRIMA DE ANTIGUEDAD ------------------------------------------------------------------

									if ($calculations['sd']>=$calculations['valorPrimaAntiguedad']) 
									{
										$calculations['primaAntiguedad'] = round($calculations['añosCompletos']*12*$calculations['valorPrimaAntiguedad'],6);
									}
									else
									{
										$calculations['primaAntiguedad'] = round($calculations['añosCompletos']*12*$calculations['sd'],6);
									}

									//  INDEMNIZACION ------------------------------------------------------------------
									$calculations['indemnizacion'] =  round($calculations['sueldoPorLiquidacion']+$calculations['veinteDiasPorAñoServicio']+$calculations['primaAntiguedad'],6);

									if ($calculations['indemnizacion'] < $calculations['valorIndemnizacionExenta']) 
									{
										$calculations['indemnizacionExcenta']	= $calculations['indemnizacion'];
									}
									else
									{
										$calculations['indemnizacionExcenta']	= $calculations['valorIndemnizacionExenta'];
									}


									if ($calculations['indemnizacion'] > $calculations['valorIndemnizacionExenta']) 
									{
										$calculations['indemnizacionGravada']	= $calculations['indemnizacion']-$calculations['indemnizacionExcenta'];
									}
									else
									{
										$calculations['indemnizacionGravada']	= 0;
									}

									$calculations['vacaciones']				= $calculations['diasParaVacaciones']*$calculations['sd'];


									// PERCEPCIONES AGUINALDO---------------------------------------------------------------------

									if (($calculations['diasParaAguinaldo'] * $calculations['sd']) < $calculations['valorAguinaldoExento']) 
									{
										$calculations['aguinaldoExento'] = $calculations['diasParaAguinaldo'] * $calculations['sd'];
									}
									else
									{
										$calculations['aguinaldoExento'] = $calculations['valorAguinaldoExento'];
									}

									if (($calculations['diasParaAguinaldo'] * $calculations['sd']) > $calculations['valorAguinaldoExento']) 
									{
										$calculations['aguinaldoGravable'] = ($calculations['diasParaAguinaldo'] * $calculations['sd'])-$calculations['aguinaldoExento'];
									}
									else
									{
										$calculations['aguinaldoGravable'] = 0;
									}


									//-------- PERCEPCIONES ---------------------------------------------------------------


									if (($calculations['vacaciones']*$calculations['prima_vac_esp'])<$calculations['valorPrimaVacacaionalExenta'])
									{
										$calculations['primaVacacionalExenta'] = round($calculations['vacaciones']*$calculations['prima_vac_esp'],6);
									}
									else
									{
										$calculations['primaVacacionalExenta'] = $calculations['valorPrimaVacacaionalExenta'];
									}

									if (($calculations['vacaciones']*$calculations['prima_vac_esp'])>$calculations['valorPrimaVacacaionalExenta'])
									{
										$calculations['primaVacacionalGravada'] = round(($calculations['vacaciones']*$calculations['prima_vac_esp'])-$calculations['primaVacacionalExenta'],6);
									}
									else
									{
										$calculations['primaVacacionalGravada'] = 0;
									}

									$calculations['otrasPercepciones'] = round($request->other_perception[$i],2);

									$calculations['totalPercepciones'] = round(round($calculations['sueldoPorLiquidacion'],2)+round($calculations['veinteDiasPorAñoServicio'],2)+round($calculations['primaAntiguedad'],2)+round($calculations['vacaciones'],2)+round($calculations['aguinaldoExento'],2)+round($calculations['aguinaldoGravable'],2)+round($calculations['primaVacacionalExenta'],2)+round($calculations['primaVacacionalGravada'],2)+round($calculations['otrasPercepciones'],2),2);
								}
								else
								{
									// VARIABLES -------------------------------------------------------
									$calculations['uma']							= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
									$calculations['salarioMinimo']					= App\Parameter::where('parameter_name','SALARY_VDF')->first()->parameter_value; 
									$calculations['valorPrimaAntiguedad']			= $calculations['salarioMinimo']*2;
									$calculations['exento']							= $calculations['uma']*90; 
									$calculations['valorAguinaldoExento']			= $calculations['uma']*30; 
									$calculations['valorPrimaVacacaionalExenta']	= $calculations['uma']*15; 
									$calculations['valorIndemnizacionExenta']		= $calculations['uma']*90;
									//  PRIMA DE ANTIGUEDAD ------------------------------------------------------------------

									if ($calculations['sd']>=$calculations['valorPrimaAntiguedad']) 
									{
										$calculations['primaAntiguedad'] = round($calculations['añosCompletos']*12*$calculations['valorPrimaAntiguedad'],6);
									}
									else
									{
										$calculations['primaAntiguedad'] = round($calculations['añosCompletos']*12*$calculations['sd'],6);
									}

									//  INDEMNIZACION  ------------------------------------------------------------------

									if ($calculations['primaAntiguedad'] < $calculations['valorIndemnizacionExenta']) 
									{
										$calculations['indemnizacionExcenta']	= $calculations['primaAntiguedad'];
									}
									else
									{
										$calculations['indemnizacionExcenta']	= $calculations['valorIndemnizacionExenta'];
									}


									if ($calculations['primaAntiguedad'] > $calculations['valorIndemnizacionExenta']) 
									{
										$calculations['indemnizacionGravada']	= $calculations['primaAntiguedad']-$calculations['indemnizacionExcenta'];
									}
									else
									{
										$calculations['indemnizacionGravada']	= 0;
									}

									$calculations['vacaciones']				= $calculations['diasParaVacaciones']*$calculations['sd'];


									// PERCEPCIONES AGUINALDO---------------------------------------------------------------------

									if (($calculations['diasParaAguinaldo'] * $calculations['sd']) < $calculations['valorAguinaldoExento']) 
									{
										$calculations['aguinaldoExento'] = $calculations['diasParaAguinaldo'] * $calculations['sd'];
									}
									else
									{
										$calculations['aguinaldoExento'] = $calculations['valorAguinaldoExento'];
									}

									if (($calculations['diasParaAguinaldo'] * $calculations['sd']) > $calculations['valorAguinaldoExento']) 
									{
										$calculations['aguinaldoGravable'] = ($calculations['diasParaAguinaldo'] * $calculations['sd'])-$calculations['aguinaldoExento'];
									}
									else
									{
										$calculations['aguinaldoGravable'] = 0;
									}


									//-------- PERCEPCIONES PRIMA VACACIONAL ---------------------------------------------------------------


									if (($calculations['vacaciones']*$calculations['prima_vac_esp'])<$calculations['valorPrimaVacacaionalExenta'])
									{
										$calculations['primaVacacionalExenta'] = round($calculations['vacaciones']*$calculations['prima_vac_esp'],6);
									}
									else
									{
										$calculations['primaVacacionalExenta'] = $calculations['valorPrimaVacacaionalExenta'];
									}

									if (($calculations['vacaciones']*$calculations['prima_vac_esp'])>$calculations['valorPrimaVacacaionalExenta'])
									{
										$calculations['primaVacacionalGravada'] = round(($calculations['vacaciones']*$calculations['prima_vac_esp'])-$calculations['primaVacacionalExenta'],6);
									}
									else
									{
										$calculations['primaVacacionalGravada'] = 0;
									}

									$calculations['otrasPercepciones'] = round($request->other_perception[$i],2);

									$calculations['totalPercepciones'] = round(round($calculations['primaAntiguedad'],2)+round($calculations['vacaciones'],2)+round($calculations['aguinaldoExento'],2)+round($calculations['aguinaldoGravable'],2)+round($calculations['primaVacacionalExenta'],2)+round($calculations['primaVacacionalGravada'],2)+round($calculations['otrasPercepciones'],2),2);

									// ------------------------------------------------------------------------------------
								}

								//-------- RETENCIONES ----------------------------------------------------------------

								// ISR 1ER FRACCION

								$calculations['baseISR_fraccion1']			= round(((($calculations['vacaciones']+$calculations['aguinaldoGravable']+$calculations['primaVacacionalGravada'])/365)*30.4)+($calculations['sd']*30),6);
								$parameterISRF1								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion1'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion1']	= $parameterISRF1->last()->inferior;
								$calculations['excedente_fraccion1']		= round($calculations['baseISR_fraccion1']-$calculations['limiteInferior_fraccion1'],6);
								$calculations['factor_fraccion1']			= round($parameterISRF1->last()->excess/100,6);
								$calculations['isrMarginal_fraccion1']		= round($calculations['excedente_fraccion1'] * $calculations['factor_fraccion1'],6);
								$calculations['cuotaFija_fraccion1']		= round($parameterISRF1->last()->quota,6);
								$calculations['isr_fraccion1']				= round($calculations['isrMarginal_fraccion1']+$calculations['cuotaFija_fraccion1'],6);

								// ISR 2DA FRACCION

								$calculations['baseISR_fraccion2']			= round($calculations['sd']*30,6);
								$parameterISRF2								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion2'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion2']	= $parameterISRF2->last()->inferior;
								$calculations['excedente_fraccion2']		= round($calculations['baseISR_fraccion2']-$calculations['limiteInferior_fraccion2'],6);
								$calculations['factor_fraccion2']			= round($parameterISRF2->last()->excess/100,6);
								$calculations['isrMarginal_fraccion2']		= round($calculations['excedente_fraccion2'] * $calculations['factor_fraccion2'],6);
								$calculations['cuotaFija_fraccion2']		= round($parameterISRF2->last()->quota,6);
								$calculations['isr_fraccion2']				= round($calculations['isrMarginal_fraccion2']+$calculations['cuotaFija_fraccion2'],6);

								$calculations['resta']		= round($calculations['isr_fraccion1']-$calculations['isr_fraccion2'],6);
								if ($calculations['resta'] == 0) 
								{
									$calculations['factor1']	= 0;
									$calculations['factor2']	= 0;
									$calculations['isr']		= 0;
								}
								else
								{
									$calculations['factor1']	= round(((($calculations['vacaciones']+$calculations['aguinaldoGravable']+$calculations['primaVacacionalGravada'])/365)*30.4),6);
									$calculations['factor2']	= round($calculations['resta']/$calculations['factor1'],6);
									$calculations['isr']		= round($calculations['factor2']*($calculations['vacaciones']+$calculations['aguinaldoGravable']+$calculations['primaVacacionalGravada']),6);
								}

								// ISR FINIQUITO (INDEMNIZACION)

								$calculations['baseTotalDePercepciones']	= round($calculations['sd']*30,6);
								$calculations['baseISR_finiquito']			= $calculations['baseTotalDePercepciones'];
								
								$parameterISRFiniquito						= App\ParameterISR::where('inferior','<=',$calculations['baseISR_finiquito'])->where('lapse',30)->get();
								
								$calculations['limiteInferior_finiquito']	= $parameterISRFiniquito->last()->inferior;
								$calculations['excedente_finiquito']		= round($calculations['baseISR_finiquito']-$calculations['limiteInferior_finiquito'],6);
								$calculations['factor_finiquito']			= round($parameterISRFiniquito->last()->excess/100,6);
								$calculations['isrMarginal_finiquito']		= round($calculations['excedente_finiquito'] * $calculations['factor_finiquito'],6);
								$calculations['cuotaFija_finiquito']		= round($parameterISRFiniquito->last()->quota,6);
								$calculations['isr_salario']				= round($calculations['isrMarginal_finiquito']+$calculations['cuotaFija_finiquito'],6);
								
								$calculations['isr_finiquito']				= round(($calculations['isr_salario']/$calculations['baseTotalDePercepciones'])*$calculations['indemnizacionGravada'],6);
								
								$calculations['totalISR']					= $calculations['isr_finiquito'] + $calculations['isr']; 
								$calculations['otrasRetenciones'] 			= round($request->other_retention[$i],2);

								if ($t_nominaemployee->workerData->first()->alimonyDiscountType != '') 
								{
									$calculations['totalRetencionesTemp']  	= round($calculations['totalISR'],2)+round($calculations['otrasRetenciones'],2);
								
									$calculations['netIncomeTemp']			= round($calculations['totalPercepciones']-$calculations['totalRetencionesTemp'],2);

									switch ($t_nominaemployee->workerData->first()->alimonyDiscountType) 
									{
										case 1: //monto
											$calculations['amountAlimony']	= $t_nominaemployee->workerData->first()->alimonyDiscount;
											$calculations['alimony']		= $calculations['amountAlimony'];
											break;

										case 2: // porcentaje
											$calculations['amountAlimony']	= $t_nominaemployee->workerData->first()->alimonyDiscount;
											$calculations['alimony']		= ($calculations['netIncomeTemp']*$calculations['amountAlimony'])/100;
											break;
										default:
											# code...
											break;
									}

									$calculations['totalRetenciones']	= round(round($calculations['totalISR'],2)+round($calculations['alimony'],2),2)+round($calculations['otrasRetenciones'],2);
									$calculations['netIncome']			= round($calculations['totalPercepciones']-$calculations['totalRetenciones'],2);
								}
								else
								{ 
									$calculations['alimony']          = 0;
									$calculations['totalRetenciones'] = round($calculations['totalISR'],2)+round($calculations['otrasRetenciones'],2);
									$calculations['netIncome']        = round($calculations['totalPercepciones']-$calculations['totalRetenciones'],2);
								}

								// --------------------------------------------------------------------------------------------
								// cambio firstOrNew
								$t_liquidation								= App\Liquidation::firstOrNew(['idnominaEmployee' => $t_nominaemployee->idnominaEmployee]);
								$t_liquidation->idnominaEmployee			= $t_nominaemployee->idnominaEmployee;
								$t_liquidation->sd							= $calculations['sd'];
								$t_liquidation->sdi							= $calculations['sdi'];
								$t_liquidation->admissionDate				= $calculations['fechaIngreso'];
								$t_liquidation->downDate					= $calculations['fechaBaja'];
								$t_liquidation->fullYears					= $calculations['añosCompletos'];
								$t_liquidation->workedDays					= $calculations['diasTrabajadosM'];
								$t_liquidation->holidayDays					= $calculations['diasParaVacaciones'];
								$t_liquidation->bonusDays					= $calculations['diasParaAguinaldo'];
								if ($request->type_payroll == '004') 
								{
									$t_liquidation->liquidationSalary			= $calculations['sueldoPorLiquidacion'];
									$t_liquidation->twentyDaysPerYearOfServices	= $calculations['veinteDiasPorAñoServicio'];
								}
								$t_liquidation->seniorityPremium			= $calculations['primaAntiguedad'];
								$t_liquidation->exemptCompensation			= $calculations['indemnizacionExcenta'];
								$t_liquidation->taxedCompensation			= $calculations['indemnizacionGravada'];
								$t_liquidation->holidays					= $calculations['vacaciones'];
								$t_liquidation->exemptBonus					= $calculations['aguinaldoExento'];
								$t_liquidation->taxableBonus				= $calculations['aguinaldoGravable'];
								$t_liquidation->holidayPremiumExempt		= $calculations['primaVacacionalExenta'];
								$t_liquidation->holidayPremiumTaxed			= $calculations['primaVacacionalGravada'];
								$t_liquidation->otherPerception				= $calculations['otrasPercepciones'];
								$t_liquidation->totalPerceptions			= $calculations['totalPercepciones'];
								$t_liquidation->other_retention				= $calculations['otrasRetenciones'];
								$t_liquidation->isr							= $calculations['totalISR'];
								$t_liquidation->totalRetentions				= $calculations['totalRetenciones'];
								$t_liquidation->netIncome					= $calculations['netIncome'];
								$t_liquidation->totalIncomeLiquidation 		= $calculationsNetIncome['netIncome'];
								$t_liquidation->alimony						= $calculations['alimony'];
								$t_liquidation->idAccountBeneficiary		= $request->idAccountBeneficiary[$i];
								if ($request->paymentWay[$i] == 1) 
								{
									if ($request->idemployeeAccount[$i] == '' && $t_nominaemployee->employee->first()->bankData()->where('visible',1)->exists()) 
									{
										$t_liquidation->idpaymentMethod		= $request->paymentWay[$i];
										$t_liquidation->idemployeeAccounts	= $t_nominaemployee->employee->first()->bankData->where('visible',1)->first()->id;
									}
									elseif ($request->idemployeeAccount[$i] != '')
									{
										$t_liquidation->idpaymentMethod		= $request->paymentWay[$i];
										$t_liquidation->idemployeeAccounts	= $request->idemployeeAccount[$i];
									}
									else 
									{
										$t_liquidation->idpaymentMethod		= 2;
										$t_liquidation->idemployeeAccounts	= null;
									}
								}
								else
								{
									$t_liquidation->idpaymentMethod		= $request->paymentWay[$i];
									$t_liquidation->idemployeeAccounts	= null;
								}
								$t_liquidation->save();

								$t_nominaemployeeaccount						= new App\NominaEmployeeAccounts();
								$t_nominaemployeeaccount->idLiquidation			= $t_liquidation->idLiquidation;
								if ($request->paymentWay[$i] == 1) 
								{
									if ($request->idemployeeAccount[$i] == '' && $t_nominaemployee->employee->first()->bankData()->where('visible',1)->exists())
									{
										$t_nominaemployeeaccount->idemployeeAccounts = $t_nominaemployee->employee->first()->bankData->where('visible',1)->first()->id;
									}
									else
									{
										$t_nominaemployeeaccount->idemployeeAccounts	= $request->idemployeeAccount[$i];
									}
								}
								else
								{
									$t_nominaemployeeaccount->idemployeeAccounts	= null;
								}
								$t_nominaemployeeaccount->save();

								$totalRequest			+= $calculations['netIncome']+$calculations['alimony'];
								$calculations			= [];
								$calculationsNetIncome	= [];

							}
							
							$t_nomina					= App\Nomina::find($t_request->nominasReal->first()->idnomina);
							$t_nomina->amount			= $totalRequest;
							$t_nomina->save();
							break;

						case '005':
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee					= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$t_nominaemployee->idCatPeriodicity	= $request->periodicity[$i];
								$t_nominaemployee->total			= $request->netIncome[$i];
								$t_nominaemployee->worked_days		= $request->worked_days[$i];
								$t_nominaemployee->save();

								// ----- calculo para dias de vacaciones ---------------------------
								$calculationsNetIncome						= [];
								$calculationsNetIncome['fechaIngreso']		= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$calculationsNetIncome['fechaActual']		= Carbon::now();
								$calculationsNetIncome['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculationsNetIncome['fechaIngreso'],$calculationsNetIncome['fechaActual']);
								$calculationsNetIncome['yearsWork']			= ceil($calculationsNetIncome['diasTrabajados']/365);
								if ($calculationsNetIncome['yearsWork'] > 24) 
								{
									$calculationsNetIncome['vacationDays']	= 20;
								}
								else
								{
									$calculationsNetIncome['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculationsNetIncome['yearsWork'])->where('toYear','>=',$calculationsNetIncome['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculationsNetIncome['yearsWork'])->where('toYear','>=',$calculationsNetIncome['yearsWork'])->first()->days : 0;
								}

								//------------------------------------------------------------------
								
								$calculationsNetIncome['prima_vac_esp']		= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
								switch ($request->periodicity[$i]) 
								{
									case '02':
										$calculationsNetIncome['divisor'] = 7;
										break;

									case '04':
										$calculationsNetIncome['divisor'] = 15;
										break;

									case '05':
										$d = new DateTime(Carbon::now());
										$calculationsNetIncome['divisor'] = App\Http\Controllers\AdministracionNominaController::days_month($d->format('m'),$d->format('Y'));
										break;
									
									default:
										break;
								}

								$calculationsNetIncome['calc_sdi']				= $request->netIncome[$i]/$calculationsNetIncome['divisor'];
								$calculationsNetIncome['sdi']					= $calculationsNetIncome['calc_sdi'];
								$calculationsNetIncome['sd']					= round($calculationsNetIncome['sdi']/((($calculationsNetIncome['vacationDays']*$calculationsNetIncome['prima_vac_esp'])+15+365)/365),2);
								$calculationsNetIncome['diasTrabajadosM']		= $request->worked_days[$i];
								$calculationsNetIncome['diasParaVacaciones']	= ($calculationsNetIncome['vacationDays']*$calculationsNetIncome['diasTrabajadosM'])/365;
								
								$calculationsNetIncome['uma']					= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
								$calculationsNetIncome['exento']				= $calculationsNetIncome['uma']*15; 

								//-------- PERCEPCIONES ---------------------------------------------------------------

								$calculationsNetIncome['vacaciones'] = $calculationsNetIncome['sd']*$calculationsNetIncome['diasParaVacaciones'];

								if (($calculationsNetIncome['sd']*$calculationsNetIncome['diasParaVacaciones']*$calculationsNetIncome['prima_vac_esp'])<$calculationsNetIncome['exento'])
								{
									$calculationsNetIncome['primaVacacionalExenta'] = round($calculationsNetIncome['sd']*$calculationsNetIncome['diasParaVacaciones']*$calculationsNetIncome['prima_vac_esp'],6);
								}
								else
								{
									$calculationsNetIncome['primaVacacionalExenta'] = $calculationsNetIncome['exento'];
								}

								if (($calculationsNetIncome['sd']*$calculationsNetIncome['diasParaVacaciones']*$calculationsNetIncome['prima_vac_esp'])>$calculationsNetIncome['exento'])
								{
									$calculationsNetIncome['primaVacacionalGravada'] = round(($calculationsNetIncome['sd']*$calculationsNetIncome['diasParaVacaciones']*$calculationsNetIncome['prima_vac_esp'])-$calculationsNetIncome['primaVacacionalExenta'],6);
								}
								else
								{
									$calculationsNetIncome['primaVacacionalGravada'] = 0;
								}

								$calculationsNetIncome['totalPercepciones']	= round(round($calculationsNetIncome['primaVacacionalExenta'],2)+round($calculationsNetIncome['primaVacacionalGravada'],2),2);
								
								$calculationsNetIncome['netIncome']			= $calculationsNetIncome['totalPercepciones'];

								$calculations					= [];
								$calculations['fechaIngreso']	= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$calculations['fechaActual']	= Carbon::now();
								$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
								$calculations['yearsWork']		= ceil($calculations['diasTrabajados']/365);
								if ($calculations['yearsWork'] > 24) 
								{
									$calculations['vacationDays']	= 20;
								}
								else
								{
									$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
								}

								//------------------------------------------------------------------
								
								$calculations['prima_vac_esp']		= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
								$calculations['sdi']				= $t_nominaemployee->workerData->first()->sdi;
								$calculations['sd']					= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);
								
								$calculations['diasTrabajadosM']	= $request->worked_days[$i];
								
								$calculations['diasParaVacaciones']	= ($calculations['vacationDays']*$calculations['diasTrabajadosM'])/365;
								
								$calculations['uma']				= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
								$calculations['exento']				= $calculations['uma']*15; 

								//-------- PERCEPCIONES ---------------------------------------------------------------

								$calculations['vacaciones'] = $calculations['sd']*$calculations['diasParaVacaciones'];

								if (($calculations['sd']*$calculations['diasParaVacaciones']*$calculations['prima_vac_esp'])<$calculations['exento'])
								{
									$calculations['primaVacacionalExenta'] = round($calculations['sd']*$calculations['diasParaVacaciones']*$calculations['prima_vac_esp'],6);
								}
								else
								{
									$calculations['primaVacacionalExenta'] = $calculations['exento'];
								}

								if (($calculations['sd']*$calculations['diasParaVacaciones']*$calculations['prima_vac_esp'])>$calculations['exento'])
								{
									$calculations['primaVacacionalGravada'] = round(($calculations['sd']*$calculations['diasParaVacaciones']*$calculations['prima_vac_esp'])-$calculations['primaVacacionalExenta'],6);
								}
								else
								{
									$calculations['primaVacacionalGravada'] = 0;
								}

								$calculations['totalPercepciones'] = round(round($calculations['primaVacacionalExenta'],2)+round($calculations['primaVacacionalGravada'],2),2);

								// ------------------------------------------------------------------------------------

								//-------- RETENCIONES ----------------------------------------------------------------

								// ISR 1ER FRACCION

								$calculations['baseISR_fraccion1']			= round((($calculations['primaVacacionalGravada']/365)*30.4)+($calculations['sd']*30),6);
								$parameterISRF1								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion1'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion1']	= $parameterISRF1->last()->inferior;
								$calculations['excedente_fraccion1']		= round($calculations['baseISR_fraccion1']-$calculations['limiteInferior_fraccion1'],6);
								$calculations['factor_fraccion1']			= round($parameterISRF1->last()->excess/100,6);
								$calculations['isrMarginal_fraccion1']		= round($calculations['excedente_fraccion1'] * $calculations['factor_fraccion1'],6);
								$calculations['cuotaFija_fraccion1']		= round($parameterISRF1->last()->quota,6);
								$calculations['isr_fraccion1']				= round($calculations['isrMarginal_fraccion1']+$calculations['cuotaFija_fraccion1'],6);

								// ISR 2DA FRACCION

								$calculations['baseISR_fraccion2']			= round($calculations['sd']*30,6);
								$parameterISRF2								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion2'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion2']	= $parameterISRF2->last()->inferior;
								$calculations['excedente_fraccion2']		= round($calculations['baseISR_fraccion2']-$calculations['limiteInferior_fraccion2'],6);
								$calculations['factor_fraccion2']			= round($parameterISRF2->last()->excess/100,6);
								$calculations['isrMarginal_fraccion2']		= round($calculations['excedente_fraccion2'] * $calculations['factor_fraccion2'],6);
								$calculations['cuotaFija_fraccion2']		= round($parameterISRF2->last()->quota,6);
								$calculations['isr_fraccion2']				= round($calculations['isrMarginal_fraccion2']+$calculations['cuotaFija_fraccion2'],6);

								$calculations['resta']		= round($calculations['isr_fraccion1']-$calculations['isr_fraccion2'],6);
								if ($calculations['resta'] == 0) 
								{
									$calculations['factor1']	= 0;
									$calculations['factor2']	= 0;
									$calculations['isr']		= 0;
								}
								else
								{
									$calculations['factor1']	= round((($calculations['primaVacacionalGravada']/365) * 30.4),6);
									$calculations['factor2']	= round($calculations['resta']/$calculations['factor1'],6);
									$calculations['isr']		= round($calculations['factor2']*$calculations['primaVacacionalGravada'],6);
								}

								if ($t_nominaemployee->workerData->first()->alimonyDiscountType != '') 
								{
									$calculations['totalRetencionesTemp']  	= round($calculations['isr'],2);
								
									$calculations['netIncomeTemp']			= round($calculations['totalPercepciones']-$calculations['totalRetencionesTemp'],2);

									switch ($t_nominaemployee->workerData->first()->alimonyDiscountType) 
									{
										case 1: //monto
											$calculations['amountAlimony']	= $t_nominaemployee->workerData->first()->alimonyDiscount;
											$calculations['alimony']		= $calculations['amountAlimony'];
											break;

										case 2: // porcentaje
											$calculations['amountAlimony']	= $t_nominaemployee->workerData->first()->alimonyDiscount;
											$calculations['alimony']		= ($calculations['netIncomeTemp']*$calculations['amountAlimony'])/100;
											break;
										default:
											# code...
											break;
									}

									$calculations['totalRetenciones']	= round($calculations['isr'],2)+round($calculations['alimony'],2);
									$calculations['netIncome']			= $calculations['totalPercepciones']-$calculations['totalRetenciones'];
								}
								else
								{ 
									$calculations['alimony']			= 0;
									$calculations['totalRetenciones'] = round($calculations['isr'],2);
								
									$calculations['netIncome']			= round($calculations['totalPercepciones']-$calculations['totalRetenciones'],2);
								}

								//------------------------------------------------------------------------------------
								// cambio firstOrNew
								$t_vacationpremium							= App\VacationPremium::firstOrNew(['idnominaEmployee' => $t_nominaemployee->idnominaEmployee]);
								$t_vacationpremium->idnominaEmployee		= $t_nominaemployee->idnominaEmployee;
								$t_vacationpremium->sd						= $calculations['sd'];
								$t_vacationpremium->sdi						= $calculations['sdi'];
								$t_vacationpremium->dateOfAdmission			= $calculations['fechaIngreso'];
								$t_vacationpremium->workedDays				= $calculations['diasTrabajadosM'];
								$t_vacationpremium->holidaysDays			= $calculations['diasParaVacaciones'];
								$t_vacationpremium->holidays				= $calculations['vacaciones'];
								$t_vacationpremium->exemptHolidayPremium	= $calculations['primaVacacionalExenta'];
								$t_vacationpremium->holidayPremiumTaxed		= $calculations['primaVacacionalGravada'];
								$t_vacationpremium->totalPerceptions		= $calculations['totalPercepciones'];
								$t_vacationpremium->isr						= $calculations['isr'];
								$t_vacationpremium->totalTaxes				= $calculations['totalRetenciones'];
								$t_vacationpremium->netIncome				= $calculations['netIncome'];
								$t_vacationpremium->totalIncomeVP 			= $calculationsNetIncome['netIncome'];
								$t_vacationpremium->alimony					= $calculations['alimony'];
								$t_vacationpremium->idAccountBeneficiary	= $request->idAccountBeneficiary[$i];
								if ($request->paymentWay[$i] == 1) 
								{
									if ($request->idemployeeAccount[$i] == '' && $t_nominaemployee->employee->first()->bankData()->where('visible',1)->exists()) 
									{
										$t_vacationpremium->idpaymentMethod		= $request->paymentWay[$i];
										$t_vacationpremium->idemployeeAccounts	= $t_nominaemployee->employee->first()->bankData->where('visible',1)->first()->id;
									}
									elseif ($request->idemployeeAccount[$i] != '')
									{
										$t_vacationpremium->idpaymentMethod		= $request->paymentWay[$i];
										$t_vacationpremium->idemployeeAccounts	= $request->idemployeeAccount[$i];
									}
									else 
									{
										$t_vacationpremium->idpaymentMethod		= 2;
										$t_vacationpremium->idemployeeAccounts	= null;
									}
								}
								else
								{
									$t_vacationpremium->idpaymentMethod		= $request->paymentWay[$i];
									$t_vacationpremium->idemployeeAccounts	= null;
								}
								$t_vacationpremium->save();

								$t_nominaemployeeaccount						= new App\NominaEmployeeAccounts();
								$t_nominaemployeeaccount->idvacationPremium		= $t_vacationpremium->idvacationPremium;
								if ($request->paymentWay[$i] == 1) 
								{
									if ($request->idemployeeAccount[$i] == '' && $t_nominaemployee->employee->first()->bankData()->where('visible',1)->exists())
									{
										$t_nominaemployeeaccount->idemployeeAccounts = $t_nominaemployee->employee->first()->bankData->where('visible',1)->first()->id;
									}
									else
									{
										$t_nominaemployeeaccount->idemployeeAccounts	= $request->idemployeeAccount[$i];
									}
								}
								else
								{
									$t_nominaemployeeaccount->idemployeeAccounts	= null;
								}
								$t_nominaemployeeaccount->save();

								$totalRequest			+= $calculations['netIncome']+$calculations['alimony'];
								$calculations			= [];
								$calculationsNetIncome	= [];

							}
							
							$t_nomina					= App\Nomina::find($t_request->nominasReal->first()->idnomina);
							$t_nomina->amount			= round($totalRequest,2);
							$t_nomina->save();
							break;

						case '006':
							$t_nomina					= App\Nomina::find($t_request->nominasReal->first()->idnomina);
							$t_nomina->ptu_to_pay		= $request->ptu_to_pay;
							$t_nomina->save();

							$sumaDiasTrabajados	= 0;
							$sumaSueldoTotal	= 0;
							//------- calculo para sumatoria de dias trabajados y sueldo total ------------------------
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{
								$t_nominaemployee				= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								
								$sumaDiasTrabajados		 		+= $request->worked_days[$i];
								$calculations					= [];
								$calculations['fechaIngreso']	= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$calculations['fechaActual']	= Carbon::now();
								$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
								$calculations['yearsWork']		= ceil($calculations['diasTrabajados']/365);
								if ($calculations['yearsWork'] > 24) 
								{
									$calculations['vacationDays']	= 20;
								}
								else
								{
									$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
								}


								$calculations['prima_vac_esp']	= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
								$calculations['sdi']			= $t_nominaemployee->workerData->first()->sdi;
								$calculations['sd']				= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);

								$sumaSueldoTotal += round($request->worked_days[$i] * $calculations['sd'],6);
								$calculations = [];
							}

							// -------------------------------------------------------------------------------------------------------
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee					= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$t_nominaemployee->worked_days		= $request->worked_days[$i];
								$t_nominaemployee->idCatPeriodicity	= $request->periodicity[$i];
								$t_nominaemployee->total			= $request->netIncome[$i];
								$t_nominaemployee->save();

								// ----- calculo para dias de vacaciones ---------------------------
								$calculationsNetIncome						= [];
								$calculationsNetIncome['fechaIngreso']		= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$calculationsNetIncome['fechaActual']		= Carbon::now();
								$calculationsNetIncome['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculationsNetIncome['fechaIngreso'],$calculationsNetIncome['fechaActual']);
								$calculationsNetIncome['yearsWork']			= ceil($calculationsNetIncome['diasTrabajados']/365);

								if ($calculationsNetIncome['yearsWork'] > 24) 
								{
									$calculationsNetIncome['vacationDays']	= 20;
								}
								else
								{
									$calculationsNetIncome['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculationsNetIncome['yearsWork'])->where('toYear','>=',$calculationsNetIncome['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculationsNetIncome['yearsWork'])->where('toYear','>=',$calculationsNetIncome['yearsWork'])->first()->days : 0;
								}

								//------------------------------------------------------------------
								
								$calculationsNetIncome['prima_vac_esp']			= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
								switch ($request->periodicity[$i]) 
								{
									case '02':
										$calculationsNetIncome['divisor'] = 7;
										break;

									case '04':
										$calculationsNetIncome['divisor'] = 15;
										break;

									case '05':
										$d = new DateTime(Carbon::now());
										$calculationsNetIncome['divisor'] = App\Http\Controllers\AdministracionNominaController::days_month($d->format('m'),$d->format('Y'));
										break;
									
									default:
										break;
								}

								$calculationsNetIncome['calc_sdi']				= $request->netIncome[$i]/$calculationsNetIncome['divisor'];
								$calculationsNetIncome['sdi']					= $calculationsNetIncome['calc_sdi'];
								$calculationsNetIncome['sd']					= round($calculationsNetIncome['sdi']/((($calculationsNetIncome['vacationDays']*$calculationsNetIncome['prima_vac_esp'])+15+365)/365),2);
								
								$calculationsNetIncome['diasTrabajadosM']		= $request->worked_days[$i];
								$calculationsNetIncome['sueldoTotal']			= round($calculationsNetIncome['diasTrabajadosM'] * $calculationsNetIncome['sd'],6);
								
								$calculationsNetIncome['sumaDiasTrabajados']	= $sumaDiasTrabajados;
								$calculationsNetIncome['sumaSueldoTotal']		= $sumaSueldoTotal;
								
								$calculationsNetIncome['uma']					= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
								$calculationsNetIncome['exento']				= $calculationsNetIncome['uma']*15; 
								
								$calculationsNetIncome['ptuPorPagar']			= round($request->ptu_to_pay,6);
								$calculationsNetIncome['factorPorDias']			= round(($calculationsNetIncome['ptuPorPagar']/2)/$calculationsNetIncome['sumaDiasTrabajados'],6);
								$calculationsNetIncome['factorPorSueldo']		= round(($calculationsNetIncome['ptuPorPagar']/2)/$calculationsNetIncome['sumaSueldoTotal'],6);
								
								$calculationsNetIncome['ptuPorDias']			= round($calculationsNetIncome['diasTrabajadosM'] * $calculationsNetIncome['factorPorDias'],6);
								$calculationsNetIncome['ptuPorSueldos']			= round($calculationsNetIncome['sueldoTotal']*$calculationsNetIncome['factorPorSueldo'],6);
								$calculationsNetIncome['ptuTotal']				= round($calculationsNetIncome['ptuPorDias']+$calculationsNetIncome['ptuPorSueldos'],6);

								//-------- PERCEPCIOONES -------------------------------------------------------------

								$calculationsNetIncome['ptuExenta']			= $calculationsNetIncome['exento'];
								$calculationsNetIncome['ptuGravada']		= round($calculationsNetIncome['ptuTotal']-$calculationsNetIncome['ptuExenta'],6);
								$calculationsNetIncome['totalPercepciones']	= round($calculationsNetIncome['ptuExenta'],2)+round($calculationsNetIncome['ptuGravada'],2);
								$calculationsNetIncome['netIncome']			= $calculationsNetIncome['totalPercepciones'];

								$calculations					= [];
								$calculations['fechaIngreso']	= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$calculations['fechaActual']	= Carbon::now();
								$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
								$calculations['yearsWork']		= ceil($calculations['diasTrabajados']/365);
								if ($calculations['yearsWork'] > 24) 
								{
									$calculations['vacationDays']	= 20;
								}
								else
								{
									$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
								}

								//------------------------------------------------------------------
								
								$calculations['prima_vac_esp']		= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
								$calculations['sdi']				= $t_nominaemployee->workerData->first()->sdi;
								$calculations['sd']					= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);
								$calculations['diasTrabajadosM']	= $request->worked_days[$i];
								$calculations['sueldoTotal']		= round($calculations['diasTrabajadosM'] * $calculations['sd'],6);
								$calculations['sumaDiasTrabajados']	= $sumaDiasTrabajados;
								$calculations['sumaSueldoTotal']	= $sumaSueldoTotal;
								$calculations['uma']				= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
								$calculations['exento']				= $calculations['uma']*15; 
								$calculations['ptuPorPagar']		= round($request->ptu_to_pay,6);
								$calculations['factorPorDias']		= round(($calculations['ptuPorPagar']/2)/$calculations['sumaDiasTrabajados'],6);
								$calculations['factorPorSueldo']	= round(($calculations['ptuPorPagar']/2)/$calculations['sumaSueldoTotal'],6);
								$calculations['ptuPorDias']			= round($calculations['diasTrabajadosM'] * $calculations['factorPorDias'],6);
								$calculations['ptuPorSueldos']		= round($calculations['sueldoTotal']*$calculations['factorPorSueldo'],6);
								$calculations['ptuTotal']			= round($calculations['ptuPorDias']+$calculations['ptuPorSueldos'],6);

								//-------- PERCEPCIOONES -------------------------------------------------------------

								$calculations['ptuExenta']			= $calculations['exento'];
								$calculations['ptuGravada']			= round($calculations['ptuTotal']-$calculations['ptuExenta'],6);
								$calculations['totalPercepciones']	= round($calculations['ptuExenta'],2)+round($calculations['ptuGravada'],2);


								// ------------------------------------------------------------------------------------

								//-------- RETENCIONES ----------------------------------------------------------------

								// ISR 1ER FRACCION

								$calculations['baseISR_fraccion1']			= round((($calculations['ptuGravada']/365)*30.4)+($calculations['sd']*30),6);
								$parameterISRF1								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion1'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion1']	= $parameterISRF1->last()->inferior;
								$calculations['excedente_fraccion1']		= round($calculations['baseISR_fraccion1']-$calculations['limiteInferior_fraccion1'],6);
								$calculations['factor_fraccion1']			= round($parameterISRF1->last()->excess/100,6);
								$calculations['isrMarginal_fraccion1']		= round($calculations['excedente_fraccion1'] * $calculations['factor_fraccion1'],6);
								$calculations['cuotaFija_fraccion1']		= round($parameterISRF1->last()->quota,6);
								$calculations['isr_fraccion1']				= round($calculations['isrMarginal_fraccion1']+$calculations['cuotaFija_fraccion1'],6);

								// ISR 2DA FRACCION

								$calculations['baseISR_fraccion2']			= round($calculations['sd']*30,6);
								$parameterISRF2								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion2'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion2']	= $parameterISRF2->last()->inferior;
								$calculations['excedente_fraccion2']		= round($calculations['baseISR_fraccion2']-$calculations['limiteInferior_fraccion2'],6);
								$calculations['factor_fraccion2']			= round($parameterISRF2->last()->excess/100,6);
								$calculations['isrMarginal_fraccion2']		= round($calculations['excedente_fraccion2'] * $calculations['factor_fraccion2'],6);
								$calculations['cuotaFija_fraccion2']		= round($parameterISRF2->last()->quota,6);
								$calculations['isr_fraccion2']				= round($calculations['isrMarginal_fraccion2']+$calculations['cuotaFija_fraccion2'],6);

								$calculations['resta']		= round($calculations['isr_fraccion1']-$calculations['isr_fraccion2'],6);
								$calculations['factor1']	= round((($calculations['ptuGravada']/365) * 30.4),6);
								if($calculations['factor1'] == 0)
								{
									$calculations['factor2']	= 0;
								}
								else
								{
									$calculations['factor2']	= round($calculations['resta']/$calculations['factor1'],6);
								}

								$calculations['isr']		= round($calculations['factor2']*$calculations['ptuGravada'],6);

								if ($t_nominaemployee->workerData->first()->alimonyDiscountType != '') 
								{
									$calculations['totalRetencionesTemp']  	= round($calculations['isr'],2);
								
									$calculations['netIncomeTemp']			= round($calculations['totalPercepciones']-$calculations['totalRetencionesTemp'],2);

									switch ($t_nominaemployee->workerData->first()->alimonyDiscountType) 
									{
										case 1: //monto
											$calculations['amountAlimony']	= $t_nominaemployee->workerData->first()->alimonyDiscount;
											$calculations['alimony']		= $calculations['amountAlimony'];
											break;

										case 2: // porcentaje
											$calculations['amountAlimony']	= $t_nominaemployee->workerData->first()->alimonyDiscount;
											$calculations['alimony']		= ($calculations['netIncomeTemp']*$calculations['amountAlimony'])/100;
											break;
										default:
											# code...
											break;
									}

									$calculations['totalRetenciones']	= round(round($calculations['isr'],2)+round($calculations['alimony'],2),2);
									$calculations['netIncome']			= round($calculations['totalPercepciones']-$calculations['totalRetenciones'],2);
								}
								else
								{ 
									$calculations['alimony']			= 0;
									$calculations['totalRetenciones'] = round($calculations['isr'],2);
								
									$calculations['netIncome']			= round($calculations['totalPercepciones']-$calculations['totalRetenciones'],2);
								}

								// --------------------------------------------------------------------------------------------
								// cambio firstOrNew								
								$t_profitsharing						= App\ProfitSharing::firstOrNew(['idnominaEmployee' => $t_nominaemployee->idnominaEmployee]);
								$t_profitsharing->idnominaEmployee		= $t_nominaemployee->idnominaEmployee;
								$t_profitsharing->sd					= $calculations['sd'];
								$t_profitsharing->sdi					= $calculations['sdi'];
								$t_profitsharing->workedDays			= $calculations['diasTrabajadosM'];
								$t_profitsharing->totalSalary			= $calculations['sueldoTotal'];
								$t_profitsharing->ptuForDays			= $calculations['ptuPorDias'];
								$t_profitsharing->ptuForSalary			= $calculations['ptuPorSueldos'];
								$t_profitsharing->totalPtu				= $calculations['ptuTotal'];
								$t_profitsharing->exemptPtu				= $calculations['ptuExenta'];
								$t_profitsharing->taxedPtu				= $calculations['ptuGravada'];
								$t_profitsharing->totalPerceptions		= $calculations['totalPercepciones'];
								$t_profitsharing->isrRetentions			= $calculations['isr'];
								$t_profitsharing->totalRetentions		= $calculations['totalRetenciones'];
								$t_profitsharing->netIncome				= $calculations['netIncome'];
								$t_profitsharing->alimony				= $calculations['alimony'];
								$t_profitsharing->idAccountBeneficiary	= $request->idAccountBeneficiary[$i];
								$t_profitsharing->totalIncomePS			= $calculationsNetIncome['netIncome'];

								if ($request->paymentWay[$i] == 1) 
								{
									if ($request->idemployeeAccount[$i] == '' && $t_nominaemployee->employee->first()->bankData()->where('visible',1)->exists()) 
									{
										$t_profitsharing->idpaymentMethod		= $request->paymentWay[$i];
										$t_profitsharing->idemployeeAccounts	= $t_nominaemployee->employee->first()->bankData->where('visible',1)->first()->id;
									}
									elseif ($request->idemployeeAccount[$i] != '')
									{
										$t_profitsharing->idpaymentMethod		= $request->paymentWay[$i];
										$t_profitsharing->idemployeeAccounts	= $request->idemployeeAccount[$i];
									}
									else 
									{
										$t_profitsharing->idpaymentMethod		= 2;
										$t_profitsharing->idemployeeAccounts	= null;
									}
								}
								else
								{
									$t_profitsharing->idpaymentMethod		= $request->paymentWay[$i];
									$t_profitsharing->idemployeeAccounts	= null;
								}
								$t_profitsharing->save();

								$t_nominaemployeeaccount					= new App\NominaEmployeeAccounts();
								$t_nominaemployeeaccount->idprofitSharing	= $t_profitsharing->idprofitSharing;
								if ($request->paymentWay[$i] == 1) 
								{
									if ($request->idemployeeAccount[$i] == '' && $t_nominaemployee->employee->first()->bankData()->where('visible',1)->exists())
									{
										$t_nominaemployeeaccount->idemployeeAccounts = $t_nominaemployee->employee->first()->bankData->where('visible',1)->first()->id;
									}
									else
									{
										$t_nominaemployeeaccount->idemployeeAccounts = $request->idemployeeAccount[$i];
									}
								}
								else
								{
									$t_nominaemployeeaccount->idemployeeAccounts	= null;
								}
								$t_nominaemployeeaccount->save();

								$totalRequest			+= $calculations['netIncome']+$calculations['alimony'];
								$calculations			= [];
								$calculationsNetIncome	= [];

							}
							
							$t_nomina					= App\Nomina::find($t_request->nominasReal->first()->idnomina);
							$t_nomina->amount			= $totalRequest;
							$t_nomina->save();
							break;

						
						default:
							# code...
							break;
					}
				}


				if ($t_request->taxPayment == 1) 
				{
					$emails = App\User::whereHas('module',function($q)
					{
						$q->where('id', 168);
					})
					->where('active',1)
					->where('notification',1)
					->get();
					$statusEmail = "Revisar";
				}

				if ($t_request->taxPayment == 0) 
				{
					if ($t_request->idDepartment == 11) 
					{
						$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 169);
						})
						->where('active',1)
						->where('notification',1)
						->get();
						$statusEmail = "Revisar";
					}
					else
					{
						$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 170);
						})
						->where('active',1)
						->where('notification',1)
						->get();
						$statusEmail = "Autorizar";
					}
				}


				$user 	=  App\User::find($request->userid);
				if ($emails != "")
				{
					try
					{
						foreach ($emails as $email)
						{
							$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to 			= $email->email;
							$kind 			= "Nómina";
							$status 		= $statusEmail;
							$date 			= Carbon::now();
							$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
							$url 			= url('administration/nomina');
							$subject 		= "Solicitud por Revisar/Autorizar";
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
						$alert	= "swal('','".Lang::get("messages.request_sent")."', 'success');";
					}
					catch(\Exception $e)
					{
						$alert	= "swal('','".Lang::get("messages.request_sent_no_mail")."', 'success');";
					}
				}

				return redirect('administration/nomina')->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/error');
		}
	}

	public function unsentNomina(Request $request,$id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$flagCheckFiscal = true;
			if ($flagCheckFiscal) 
			{
				$flag                 = false;
				$t_request            = App\RequestModel::find($id);
				$t_request->idRequest = $request->userid;
				$t_request->status = 2;
				$t_request->save();
				$totalRequest               = 0;
				$t_nomina                   = App\Nomina::find($t_request->nominasReal->first()->idnomina);
				$t_nomina->title            = $request->title;
				$t_nomina->datetitle        = $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
				$t_nomina->idCatTypePayroll = $request->type_payroll;
				$t_nomina->from_date        = $request->from_date_request	!= "" ? Carbon::createFromFormat('d-m-Y',$request->from_date_request)->format('Y-m-d') 	: null;
				$t_nomina->to_date          = $request->to_date_request		!= "" ? Carbon::createFromFormat('d-m-Y',$request->to_date_request)->format('Y-m-d') 	: null;
				$t_nomina->down_date        = $request->down_date_request	!= "" ? Carbon::createFromFormat('d-m-Y',$request->down_date_request)->format('Y-m-d') 	: null;
				$t_nomina->idCatPeriodicity = $request->periodicity_request;
				$t_nomina->ptu_to_pay		= $request->ptu_to_pay;
				
				if($t_request->taxPayment == 0) 
				{
					if($t_request->nominasReal->first()->type_nomina == 2)
					{
						if(isset($request->deleteEmployee) && $request->deleteEmployee != '')
						{
							for($i = 0; $i < count($request->deleteEmployee); $i++) 
							{
								$deleteNF = App\NominaEmployeeNF::where('idnominaEmployee',$request->deleteEmployee[$i])->delete();
								$delete   = App\NominaEmployee::find($request->deleteEmployee[$i])->delete();
							}
						}
						for($i = 0; $i < count($request->request_idnominaemployeenf); $i++) 
						{
							$t_nominaemployee                   = App\NominaEmployee::find($request->request_idnominaEmployee[$i]);
							$t_nominaemployee->from_date        = $request->from_date_request	!= "" ? Carbon::createFromFormat('d-m-Y',$request->from_date_request)->format('Y-m-d')	: null;
							$t_nominaemployee->to_date          = $request->to_date_request		!= "" ? Carbon::createFromFormat('d-m-Y',$request->to_date_request)->format('Y-m-d')	: null;
							$t_nominaemployee->idCatPeriodicity = $request->periodicity_request;
							$t_nominaemployee->absence          = $request->absence[$i];
							$t_nominaemployee->extra_hours      = $request->extraHours[$i];
							$t_nominaemployee->holidays         = $request->holidays[$i];
							$t_nominaemployee->sundays          = $request->sundays[$i];
							$t_nominaemployee->save();
							if ($request->request_idnominaemployeenf[$i] == 'x') 
							{
								$t_nominaemployeenf                   = new App\NominaEmployeeNF();
							}
							else
							{
								$t_nominaemployeenf						= App\NominaEmployeeNF::find($request->request_idnominaemployeenf[$i]);
								App\DiscountsNomina::where('idnominaemployeenf',$request->request_idnominaemployeenf[$i])->delete();
							}
							$t_nominaemployeenf->idnominaEmployee = $request->request_idnominaEmployee[$i];
							if($request->request_paymentWay[$i] == 1) 
							{
								if ($request->request_idemployeeAccount[$i] == '' && $t_nominaemployee->employee->first()->bankData()->where('visible',1)->exists()) 
								{
									$t_nominaemployeenf->idpaymentMethod	= $request->request_paymentWay[$i];
									$t_nominaemployeenf->idemployeeAccounts	= $t_nominaemployee->employee->first()->bankData->where('visible',1)->first()->id;
								}
								elseif ($request->request_idemployeeAccount[$i] != '')
								{
									$t_nominaemployeenf->idpaymentMethod    = $request->request_paymentWay[$i];
									$t_nominaemployeenf->idemployeeAccounts = $request->request_idemployeeAccount[$i];
								}
								else 
								{
									$t_nominaemployeenf->idpaymentMethod    = 2;
									$t_nominaemployeenf->idemployeeAccounts =  null;
								}
							}
							else
							{
								$t_nominaemployeenf->idpaymentMethod    = $request->request_paymentWay[$i];
								$t_nominaemployeenf->idemployeeAccounts = null;
							}
							$t_nominaemployeenf->reference = $request->request_reference[$i];

							if ($t_nomina->idCatTypePayroll == '001') 
							{
								$t_nominaemployeenf->extra_time			= $request->total_extra_time_no_fiscal[$i];
								$t_nominaemployeenf->holiday			= $request->total_holiday_no_fiscal[$i];
								$t_nominaemployeenf->sundays			= $request->total_sundays_no_fiscal[$i];
								$t_nominaemployeenf->complementPartial	= $request->sueldo_total_no_fiscal[$i];
								$t_nominaemployeenf->amount				= $request->total_no_fiscal_por_pagar[$i];
								$t_nominaemployeenf->netIncome    		= $request->request_netIncome[$i];
								$t_nominaemployeenf->save();
							}
							else
							{

								if($request->type_nf == 1) 
								{
									$t_nominaemployeenf->complementPartial = $request->request_netIncome[$i];
									$t_nominaemployeenf->amount            = $request->request_netIncome[$i];
								}
								else
								{
									$t_nominaemployeenf->complementPartial = $request->request_netIncome[$i];
									$t_nominaemployeenf->amount            = $request->request_netIncome[$i];

									$t_nominaemployeenf->netIncome    = $request->request_netIncome[$i];
									$t_nominaemployeenf->reasonAmount = $request->request_reason_payment[$i];
									$t_nominaemployeenf->save();
								}
							}

							if ($request->infonavit_complemento[$i] > 0) 
							{
								$t_discount                     = new App\DiscountsNomina();
								$t_discount->amount             = $request->infonavit_complemento[$i];
								$t_discount->reason             = 'INFONAVIT parte fiscal';
								$t_discount->idnominaemployeenf = $t_nominaemployeenf->idnominaemployeenf;
								$t_discount->save();
							}
							$t_nominaemployeenf->save();
						}

						foreach ($t_nomina->nominaEmployee as $n) 
						{
							$totalRequest += $n->nominasEmployeeNF->first()->amount;
						}

						$t_nomina->amount = $totalRequest;
					}
					// NOM35 
					if ($t_request->nominasReal->first()->type_nomina == 3) 
					{
						if (isset($request->deleteEmployee) && $request->deleteEmployee != '') 
						{
							for ($i=0; $i < count($request->deleteEmployee); $i++) 
							{ 
								$deleteNF	= App\NominaEmployeeNF::where('idnominaEmployee',$request->deleteEmployee[$i])->delete();
								$delete		= App\NominaEmployee::find($request->deleteEmployee[$i])->delete();
							}
						}
						for ($i=0; $i < count($request->request_idnominaemployeenf); $i++) 
						{ 
							$t_nominaemployee					= App\NominaEmployee::find($request->request_idnominaEmployee[$i]);
							$t_nominaemployee->from_date		= $request->from_date_request	!= "" ? Carbon::createFromFormat('d-m-Y',$request->from_date_request)->format('Y-m-d')	: null;
							$t_nominaemployee->to_date			= $request->to_date_request		!= "" ? Carbon::createFromFormat('d-m-Y',$request->to_date_request)->format('Y-m-d') 	: null;
							$t_nominaemployee->idCatPeriodicity	= $request->periodicity_request;
							$t_nominaemployee->absence			= $request->absence[$i];
							$t_nominaemployee->extra_hours		= $request->extraHours[$i];
							$t_nominaemployee->holidays			= $request->holidays[$i];
							$t_nominaemployee->sundays			= $request->sundays[$i];
							$t_nominaemployee->save();
							if ($request->request_idnominaemployeenf[$i] == 'x') 
							{
								$t_nominaemployeenf						= new App\NominaEmployeeNF();
							}
							else
							{
								$t_nominaemployeenf						= App\NominaEmployeeNF::find($request->request_idnominaemployeenf[$i]);
								App\DiscountsNomina::where('idnominaemployeenf',$request->request_idnominaemployeenf[$i])->delete();
							}
							$t_nominaemployeenf->idnominaEmployee	= $request->request_idnominaEmployee[$i];
							
							if ($request->request_paymentWay[$i] == 1) 
							{
								if ($request->request_idemployeeAccount[$i] == '' && $t_nominaemployee->employee->first()->bankData()->where('visible',1)->exists()) 
								{
									$t_nominaemployeenf->idpaymentMethod	= $request->request_paymentWay[$i];
									$t_nominaemployeenf->idemployeeAccounts	= $t_nominaemployee->employee->first()->bankData->where('visible',1)->first()->id;
								}
								elseif ($request->request_idemployeeAccount[$i] != '')
								{
									$t_nominaemployeenf->idpaymentMethod	= $request->request_paymentWay[$i];
									$t_nominaemployeenf->idemployeeAccounts	= $request->request_idemployeeAccount[$i];
								}
								else 
								{
									$t_nominaemployeenf->idpaymentMethod	= 2;
									$t_nominaemployeenf->idemployeeAccounts	= null;
								}
							}
							else
							{
								$t_nominaemployeenf->idpaymentMethod	= $request->request_paymentWay[$i];
								$t_nominaemployeenf->idemployeeAccounts	= null;
							}

							$t_nominaemployeenf->reference			= $request->request_reference[$i];
							
							if ($t_nomina->idCatTypePayroll == '001') 
							{
								$t_nominaemployeenf->extra_time			= $request->total_extra_time_no_fiscal[$i];
								$t_nominaemployeenf->holiday			= $request->total_holiday_no_fiscal[$i];
								$t_nominaemployeenf->sundays			= $request->total_sundays_no_fiscal[$i];
								$t_nominaemployeenf->complementPartial	= $request->sueldo_total_no_fiscal[$i];
								$t_nominaemployeenf->amount				= $request->total_no_fiscal_por_pagar[$i];
								$t_nominaemployeenf->netIncome    		= $request->request_netIncome[$i];
								$t_nominaemployeenf->save();
							}
							else
							{

								if($request->type_nf == 1) 
								{
									$t_nominaemployeenf->complementPartial = $request->request_netIncome[$i];
									$t_nominaemployeenf->amount            = $request->request_netIncome[$i];
								}
								else
								{
									$t_nominaemployeenf->complementPartial = $request->request_netIncome[$i];
									$t_nominaemployeenf->amount            = $request->request_netIncome[$i];

									$t_nominaemployeenf->netIncome    = $request->request_netIncome[$i];
									$t_nominaemployeenf->reasonAmount = $request->request_reason_payment[$i];
									$t_nominaemployeenf->save();
								}
							}

							if ($request->infonavit_complemento[$i] > 0) 
							{
								$t_discount                     = new App\DiscountsNomina();
								$t_discount->amount             = $request->infonavit_complemento[$i];
								$t_discount->reason             = 'INFONAVIT parte fiscal';
								$t_discount->idnominaemployeenf = $t_nominaemployeenf->idnominaemployeenf;
								$t_discount->save();
							}
							$t_nominaemployeenf->save();
							
						}

						foreach ($t_nomina->nominaEmployee as $n) 
						{
							$totalRequest += $n->nominasEmployeeNF->first()->amount;
						}

						$t_nomina->amount = $totalRequest;
					}
				}
				$t_nomina->save();
				if ($t_request->taxPayment == 1)
				{
					if(isset($request->deleteEmployee) && $request->deleteEmployee != '') 
					{
						for ($i = 0; $i < count($request->deleteEmployee); $i++)
						{ 
							$delete = App\NominaEmployee::find($request->deleteEmployee[$i])->delete();
						}
					}
					switch($request->type_payroll)
					{
						case '001':
							if(!empty($request->idnominaEmployee_request))
							{
								for($i = 0; $i < count($request->idnominaEmployee_request); $i++)
								{ 
									$t_nominaemployee                   = App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
									$t_nominaemployee->from_date        = $request->from_date[$i]	!= "" ? Carbon::createFromFormat('d-m-Y',$request->from_date[$i])->format('Y-m-d')	: null;
									$t_nominaemployee->to_date          = $request->to_date[$i]		!= "" ? Carbon::createFromFormat('d-m-Y',$request->to_date[$i])->format('Y-m-d')	: null;
									$t_nominaemployee->idCatPeriodicity = $request->periodicity[$i];
									$t_nominaemployee->absence          = $request->absence[$i];
									$t_nominaemployee->extra_hours      = $request->extraHours[$i];
									$t_nominaemployee->holidays         = $request->holidays[$i];
									$t_nominaemployee->loan_perception  = $request->loan_perception[$i];
									$t_nominaemployee->loan_retention   = $request->loan_retention[$i];
									$t_nominaemployee->idpaymentMethod  = $request->paymentWay[$i];
									$t_nominaemployee->sundays          = $request->sundays[$i];
									$t_nominaemployee->save();

									$nom_no_fiscal 	= App\RequestModel::where('kind',16)
											->where('idprenomina',$t_request->idprenomina)
											->where('idDepartment',$t_request->idDepartment)
											->where('taxPayment',0)
											->get();

									if($nom_no_fiscal != '')
									{
										foreach ($nom_no_fiscal as $request_nf) 
										{
											$nom_emp_nf = App\NominaEmployee::where('idrealEmployee',$t_nominaemployee->idrealEmployee)
														->where('idnomina',$request_nf->nominasReal->first()->idnomina)
														->first();

											if ($nom_emp_nf != "") 
											{
												$nom_emp_nf->from_date        = $request->from_date[$i]	!= "" ? Carbon::createFromFormat('d-m-Y',$request->from_date[$i])->format('Y-m-d')	: null;
												$nom_emp_nf->to_date          = $request->to_date[$i]	!= "" ? Carbon::createFromFormat('d-m-Y',$request->to_date[$i])->format('Y-m-d') 	: null;
												$nom_emp_nf->idCatPeriodicity = $request->periodicity[$i];
												$nom_emp_nf->absence          = $request->absence[$i];
												$nom_emp_nf->extra_hours      = $request->extraHours[$i];
												$nom_emp_nf->holidays         = $request->holidays[$i];
												$nom_emp_nf->sundays          = $request->sundays[$i];
												$nom_emp_nf->save();
											}

										}
									}
								}
							}
							break;
						case '002':
							if(!empty($request->idnominaEmployee_request))
							{
								for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
								{
									$t_nominaemployee                   = App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
									$t_nominaemployee->day_bonus        = $request->day_bonus[$i];
									$t_nominaemployee->idCatPeriodicity = $request->periodicity[$i];
									$t_nominaemployee->total            = $request->netIncome[$i];
									$t_nominaemployee->idpaymentMethod  = $request->paymentWay[$i];
									$t_nominaemployee->save();
								}
							}
							break;
						case '003':
						case '004':
							if(!empty($request->idnominaEmployee_request))
							{
								for ($i = 0; $i < count($request->idnominaEmployee_request); $i++) 
								{
									$t_nominaemployee                   = App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
									$t_nominaemployee->down_date        = $request->down_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->down_date[$i])->format('Y-m-d') : null;
									$t_nominaemployee->worked_days      = $request->worked_days[$i];
									$t_nominaemployee->other_perception = $request->other_perception[$i];
									$t_nominaemployee->other_retention 	= $request->other_retention[$i];
									$t_nominaemployee->idCatPeriodicity = $request->periodicity[$i];
									$t_nominaemployee->total            = $request->netIncome[$i];
									$t_nominaemployee->idpaymentMethod  = $request->paymentWay[$i];
									$t_nominaemployee->save();
								}
							}
							break;
						case '005':
							if(!empty($request->idnominaEmployee_request))
							{
								for ($i = 0; $i < count($request->idnominaEmployee_request); $i++) 
								{
									$t_nominaemployee                   = App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
									$t_nominaemployee->idCatPeriodicity = $request->periodicity[$i];
									$t_nominaemployee->total            = $request->netIncome[$i];
									$t_nominaemployee->worked_days      = $request->worked_days[$i];
									$t_nominaemployee->idpaymentMethod  = $request->paymentWay[$i];
									$t_nominaemployee->save();
								}
							}
							break;
						case '006':
							$t_nomina             = App\Nomina::find($t_request->nominasReal->first()->idnomina);
							$t_nomina->ptu_to_pay = $request->ptu_to_pay;
							$t_nomina->save();
							$sumaDiasTrabajados = 0;
							$sumaSueldoTotal    = 0;
							if(!empty($request->idnominaEmployee_request))
							{
								for($i = 0; $i < count($request->idnominaEmployee_request); $i++)
								{
									$t_nominaemployee                   = App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
									$t_nominaemployee->worked_days      = $request->worked_days[$i];
									$t_nominaemployee->idCatPeriodicity = $request->periodicity[$i];
									$t_nominaemployee->total            = $request->netIncome[$i];
									$t_nominaemployee->idpaymentMethod  = $request->paymentWay[$i];
									$t_nominaemployee->save();
								}
							}
							break;
					}
				}
				if($request->routeExcel == "")
				{
					$alert	= "swal('','".Lang::get("messages.request_saved")."', 'success');";	
					return redirect('administration/nomina/nomina-create/'.$id)->with('alert',$alert);
				}
				else
				{
					$routeExcel = $request->routeExcel;
					return back()->with(compact('routeExcel'));
				}
			}
		}
		else
		{
			return redirect('/error');
		}
	}

	public function updateNominaNF(Request $request,$id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$flag = false;
			$request_type	= App\RequestModel::find($id);
			if ($request_type->taxPayment == 0) 
			{
				for ($i=0; $i < count($request->request_idnominaemployeenf); $i++) 
				{ 
					if ($request->request_paymentWay[$i] == '' || $request->request_paymentWay[$i] == null) 
					{
						$t_nominaemployee	= App\RealEmployee::find($request->idrealEmployee[$i]);
						$alert				= 'swal("","Error, revise que el empleado '.$t_nominaemployee->name.' '.$t_nominaemployee->last_name.' '.$t_nominaemployee->scnd_last_name.' tenga una forma de pago","error");';
						return back()->with('alert',$alert);
					}

					if ($request->request_paymentWay[$i] == 1 && ($request->request_idemployeeAccount[$i] == null || $request->request_idemployeeAccount[$i] == '' || $request->request_amount[$i] == '' || $request->request_amount[$i] == null)) 
					{
						$t_nominaemployee	= App\RealEmployee::find($request->idrealEmployee[$i]);
						$alert				= 'swal("","Error, revise que el empleado '.$t_nominaemployee->name.' '.$t_nominaemployee->last_name.' '.$t_nominaemployee->scnd_last_name.' tenga una cuenta dada de alta o cambie la forma de pago a Efectivo","error");';
						return back()->with('alert',$alert);
					}

					if ($request->request_paymentWay[$i] == 2 && $request->request_amount[$i] == '' || $request->request_amount[$i] == null) 
					{
						$t_nominaemployee	= App\RealEmployee::find($request->idrealEmployee[$i]);
						$alert				= 'swal("","Error, revise que el empleado '.$t_nominaemployee->name.' '.$t_nominaemployee->last_name.' '.$t_nominaemployee->scnd_last_name.' cuente con los datos: Importe'.$request->request_paymentWay[$i].'-'.$request->request_amount[$i].' ","error");';
						return back()->with('alert',$alert);
					}
				}
			}

			$t_request				= App\RequestModel::find($id);
			$t_request->idRequest	= $request->userid;
			if ($t_request->idDepartment == 11) 
			{
				$t_request->status	= 14;
			}
			else
			{
				$t_request->status	= 3;
			}
			
			$t_request->save();

			$totalRequest = 0;

			$t_nomina					= App\Nomina::find($t_request->nominasReal->first()->idnomina);
			$t_nomina->title			= $request->title;
			$t_nomina->datetitle		= $request->datetitle != '' ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_nomina->idCatTypePayroll	= $request->type_payroll;
			$t_nomina->from_date 		= $request->from_date_request	!= "" ? Carbon::createFromFormat('d-m-Y',$request->from_date_request)->format('Y-m-d')	: null;
			$t_nomina->to_date 			= $request->to_date_request 	!= "" ? Carbon::createFromFormat('d-m-Y',$request->to_date_request)->format('Y-m-d') 	: null;
			$t_nomina->down_date 		= $request->down_date_request	!= "" ? Carbon::createFromFormat('d-m-Y',$request->down_date_request)->format('Y-m-d') 	: null;
			$t_nomina->idCatPeriodicity	= $request->periodicity_request;
			if ($t_request->taxPayment == 0) 
			{	
				if (isset($request->deleteEmployee) && $request->deleteEmployee != '') 
				{
					for ($i=0; $i < count($request->deleteEmployee); $i++) 
					{ 
						$deleteNF	= App\NominaEmployeeNF::where('idnominaEmployee',$request->deleteEmployee[$i])->delete();
						$delete		= App\NominaEmployee::find($request->deleteEmployee[$i])->delete();
					}
				}
				for ($i=0; $i < count($request->request_idnominaemployeenf); $i++) 
				{ 
					if ($request->request_idnominaemployeenf[$i] == 'x') 
					{
						$t_nominaemployeenf						= new App\NominaEmployeeNF();
						$t_nominaemployeenf->idnominaEmployee	= $request->request_idnominaEmployee[$i];
						$t_nominaemployeenf->idpaymentMethod	= $request->request_paymentWay[$i];
						$t_nominaemployeenf->idemployeeAccounts	= $request->request_idemployeeAccount[$i];
						$t_nominaemployeenf->reference			= $request->request_reference[$i];
						$t_nominaemployeenf->discount			= $request->request_discount[$i];
						$t_nominaemployeenf->reasonDiscount		= $request->request_reason_discount[$i];
						$t_nominaemployeenf->amount				= $request->request_amount[$i];
						$t_nominaemployeenf->reasonAmount		= $request->request_reason_payment[$i];
						$t_nominaemployeenf->save();
					}
				}

				foreach ($t_nomina->nominaEmployee as $n) 
				{
					$totalRequest += $n->nominasEmployeeNF->first()->amount;
				}

				$t_nomina->amount = $totalRequest;
			}
			$t_nomina->save();

			$alert	= "swal('','".Lang::get("messages.request_sent")."', 'success');";

			return redirect('administration/nomina')->with('alert',$alert);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function getDataNomina(Request $request)
	{
		if ($request->ajax()) 
		{
			if($request->idnominaEmployee!= '')
			{
				$folio			= $request->folio;
				$type_payroll 	= $request->type_payroll;
				$nominaemployee	= App\NominaEmployee::find($request->idnominaEmployee);
				return view('administracion.nomina.modal.datos-nomina',['nominaemployee' => $nominaemployee,'folio' => $folio,'type_payroll'=>$type_payroll]);
			}
		}
	}

	public function updateDataNomina(Request $request)
	{
		switch ($request->type_payroll) 
		{
			case '001':
				$t_nominaemployee                   = App\NominaEmployee::find($request->idnominaEmployee);
				$t_nominaemployee->from_date        = $request->from_date_edit	!= "" ? Carbon::createFromFormat('d-m-Y',$request->from_date_edit)->format('Y-m-d') : null;
				$t_nominaemployee->to_date          = $request->to_date_edit	!= "" ? Carbon::createFromFormat('d-m-Y',$request->to_date_edit)->format('Y-m-d') 	: null;
				$t_nominaemployee->idCatPeriodicity = $request->periodicity_edit;
				$t_nominaemployee->absence          = $request->absence_edit;
				$t_nominaemployee->extra_hours      = $request->extra_hours_edit;
				$t_nominaemployee->holidays         = $request->holidays_edit;
				$t_nominaemployee->sundays          = $request->sundays_edit;
				$t_nominaemployee->loan_perception  = $request->loan_perception_edit;
				$t_nominaemployee->loan_retention   = $request->loan_retention_edit;
				$t_nominaemployee->save();

				$t_nomina 		= App\Nomina::find($t_nominaemployee->idnomina);
				$req			= App\RequestModel::find($t_nomina->idFolio);
				$nom_no_fiscal 	= App\RequestModel::where('kind',16)
								->where('idprenomina',$req->idprenomina)
								->where('idDepartment',$req->idDepartment)
								->where('taxPayment',0)
								->get();

				if($nom_no_fiscal != '')
				{
					foreach ($nom_no_fiscal as $request_nf) 
					{
						$nom_emp_nf = App\NominaEmployee::where('idrealEmployee',$t_nominaemployee->idrealEmployee)
									->where('idnomina',$request_nf->nominasReal->first()->idnomina)
									->first();

						if ($nom_emp_nf != "") 
						{
							if ($request_nf->status == 2) 
							{
								$nom_emp_nf->absence		= $request->absence_edit;
								$nom_emp_nf->extra_hours	= $request->extra_hours_edit;
								$nom_emp_nf->holidays		= $request->holidays_edit;
								$nom_emp_nf->sundays		= $request->sundays_edit;
								$nom_emp_nf->save();
							}
						}
					}
				}	

				if (!App\Http\Controllers\AdministracionNominaController::recalculateNomina($request->type_payroll,$request->idnominaEmployee)) 
				{
					$alert	= "swal('', '".Lang::get("messages.failed_to_recalculate")."', 'error');";
				}
				else
				{
					$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
				}
				break;

			case '002':
				$t_nominaemployee					= App\NominaEmployee::find($request->idnominaEmployee);
				$t_nominaemployee->day_bonus		= $request->day_bonus_edit;
				$t_nominaemployee->idCatPeriodicity	= $request->periodicity_edit;
				$t_nominaemployee->total			= $request->netIncome_edit;
				$t_nominaemployee->save();
				if (!App\Http\Controllers\AdministracionNominaController::recalculateNomina($request->type_payroll,$request->idnominaEmployee)) 
				{
					$alert	= "swal('', '".Lang::get("messages.failed_to_recalculate")."', 'error');";
				}
				else
				{
					$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
				}
				break;

			case '003':
			case '004':
				$t_nominaemployee					= App\NominaEmployee::find($request->idnominaEmployee);
				$t_nominaemployee->worked_days		= $request->worked_days_edit;
				$t_nominaemployee->down_date		= $request->down_date_edit != "" ? Carbon::createFromFormat('d-m-Y',$request->down_date_edit)->format('Y-m-d') : null;
				$t_nominaemployee->other_perception	= $request->otherPerception_edit;
				$t_nominaemployee->idCatPeriodicity	= $request->periodicity_edit;
				$t_nominaemployee->total			= $request->netIncome_edit;
				$t_nominaemployee->save();
				if (!App\Http\Controllers\AdministracionNominaController::recalculateNomina($request->type_payroll,$request->idnominaEmployee)) 
				{
					$alert	= "swal('', '".Lang::get("messages.failed_to_recalculate")."', 'error');";
				}
				else
				{
					$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
				}
				break;

			case '005':
				$t_nominaemployee					= App\NominaEmployee::find($request->idnominaEmployee);
				$t_nominaemployee->worked_days		= $request->worked_days_edit;
				$t_nominaemployee->idCatPeriodicity	= $request->periodicity_edit;
				$t_nominaemployee->total			= $request->netIncome_edit;
				$t_nominaemployee->save();
				if (!App\Http\Controllers\AdministracionNominaController::recalculateNomina($request->type_payroll,$request->idnominaEmployee)) 
				{
					$alert	= "swal('', '".Lang::get("messages.failed_to_recalculate")."', 'error');";
				}
				else
				{
					$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
				}
				break;

			case '006':
				$t_nominaemployee					= App\NominaEmployee::find($request->idnominaEmployee);
				$t_nominaemployee->worked_days		= $request->worked_days_edit;
				$t_nominaemployee->idCatPeriodicity	= $request->periodicity_edit;
				$t_nominaemployee->total			= $request->netIncome_edit;
				$t_nominaemployee->save();
				if (!App\Http\Controllers\AdministracionNominaController::recalculateNomina($request->type_payroll,$request->idnominaEmployee)) 
				{
					$alert	= "swal('', '".Lang::get("messages.failed_to_recalculate")."', 'error');";
				}
				else
				{
					$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
				}
				break;
		}
		
		$r = App\RequestModel::find($request->folio);
		switch ($r->status) 
		{
			case 2:
				return redirect()->route('nomina.nomina-create',['id'=>$request->folio])->with('alert',$alert);
				break;

			case 3:
				return redirect()->route('nomina.nomina-review',['id'=>$request->folio])->with('alert',$alert);
				break;

			case 15:
				return redirect()->route('nomina.nomina-authorization',['id'=>$request->folio])->with('alert',$alert);
				break;

			case 14:
				return redirect()->route('nomina.nomina-constructionreview',['id'=>$request->folio])->with('alert',$alert);
				break;

			case 4:
				return redirect()->route('nomina.nomina-authorization',['id'=>$request->folio])->with('alert',$alert);
				break;
			
			default:
				# code...
				break;
		}
	}

	public function nominaFollowSearch(Request $request)
	{
		if(Auth::user()->module->where('id',167)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',167)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',167)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}

			$data			= App\Module::find($this->module_id);
			$titleRequest	= $request->titleRequest;
			$name			= $request->name;
			$folio			= $request->folio;
			$department		= $request->department;
			$mindate		= $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate		= $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$fiscal			= $request->fiscal;
			$type 			= $request->type;
			$idEmployee 	= $request->idEmployee;
			$status 		= $request->status;
			
			$requests = App\RequestModel::where('kind',16)
						->where(function ($q) use ($global_permission)
						{
							if ($global_permission == 0) 
							{
								$q->where('idElaborate',Auth::user()->id)
									->orWhere('idRequest',Auth::user()->id)
									->orWhere('idCheck',Auth::user()->id)
									->orWhere('idCheckConstruction',Auth::user()->id)
									->orWhere('idAuthorize',Auth::user()->id);
							}
						})
						->where(function ($query) use ($name, $folio, $titleRequest, $fiscal, $department,$mindate,$maxdate,$type,$idEmployee,$status)
						{
							if($name != "")
							{
								$query->whereHas('requestUser',function($q) use($name)
								{
									$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
								});
							}
							if($folio != "")
							{
								$query->where('folio',$folio);
							}
							if($titleRequest != "")
							{
								$query->whereHas('nomina',function($q) use($titleRequest)
								{
									$q->where('title','LIKE','%'.preg_replace("/\s+/", "%", $titleRequest).'%');
								});
							}
							if($idEmployee != "")
							{
								$query->whereHas('nomina',function($q) use($idEmployee)
								{
									$q->whereHas('nominaEmployee',function($q) use($idEmployee)
									{
										$q->whereIn('idrealEmployee',$idEmployee);
									});
								});
							}
							if ($fiscal != "") 
							{
								$query->whereHas('nominasReal',function($q) use ($fiscal)
								{
									$q->whereIn('type_nomina',$fiscal);
								});
							}
							if ($department) 
							{
								$query->where('idDepartment',$department);
							}
							if($mindate != "" && $maxdate != "")
							{
								$query->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
							}
							if ($type != "") 
							{
								$query->whereHas('nominasReal',function($q) use ($type)
								{
									$q->where('idCatTypePayroll',$type);
								});
							}
							if($status != "")
							{
								$query->whereIn('status',$status);
							}
						})
						->orderBy('fDate','DESC')
						->orderBy('folio','DESC')
						->paginate(10);
			
			return view('administracion.nomina.busqueda-seguimiento',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 167,
					'requests'		=> $requests,
					'name'			=> $name, 
					'mindate'		=> $request->mindate,
					'maxdate'		=> $request->maxdate,
					'folio'			=> $folio,
					'fiscal'		=> $fiscal,
					'titleRequest'	=> $titleRequest,
					'department'	=> $department,
					'type' 			=> $type,
					'idEmployee' 	=> $idEmployee,
					'status' 		=> $status
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function receipt_download(App\PayrollReceipt $receipt)
	{
		if (Auth::user()->module->where('id',167)->count()>0) 
		{
			return \Storage::disk('reserved')->download($receipt->path);
		}
	}

	public function nominaFollow(Request $request, $id)
	{
		if (Auth::user()->module->where('id',167)->count()>0) 
		{
			$data = App\Module::find($this->module_id);
			$request = App\RequestModel::find($id);

			if ($request != '') 
			{
				return view('administracion.nomina.alta-nomina',[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 167,
					'request'	=> $request
				]);
			}
			else
			{
				return redirect('error');
			}
		}
	}

	public function reviewSearch(Request $request)
	{
		if(Auth::user()->module->where('id',168)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$titleRequest	= $request->titleRequest;
			$name			= $request->name;
			$folio			= $request->folio;
			$department		= $request->department;
			$mindate		= $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate		= $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$fiscal			= $request->fiscal;
			$type 			= $request->type;
			$idEmployee 	= $request->idEmployee;

			$requests = App\RequestModel::where('kind',16)
						->whereIn('status',[3])
						->where(function ($query) use ($name, $folio, $titleRequest, $fiscal, $department,$mindate,$maxdate,$type,$idEmployee)
						{
							if($name != "")
							{
								$query->whereHas('requestUser',function($q) use($name)
								{
									$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
								});
							}
							if($folio != "")
							{
								$query->where('folio',$folio);
							}
							if($titleRequest != "")
							{
								$query->whereHas('nomina',function($q) use($titleRequest)
								{
									$q->where('title','LIKE','%'.preg_replace("/\s+/", "%", $titleRequest).'%');
								});
							}
							if($idEmployee != "")
							{
								$query->whereHas('nomina',function($q) use($idEmployee)
								{
									$q->whereHas('nominaEmployee',function($q) use($idEmployee)
									{
										$q->whereIn('idrealEmployee',$idEmployee);
									});
								});
							}
							if ($fiscal != "") 
							{
								$query->whereHas('nominasReal',function($q) use ($fiscal)
								{
									$q->whereIn('type_nomina',$fiscal);
								});
							}
							if ($department) 
							{
								$query->where('idDepartment',$department);
							}
							if($mindate != "" && $maxdate != "")
							{
								$query->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
							}
							if ($type != "") 
							{
								$query->whereHas('nominasReal',function($q) use ($type)
								{
									$q->where('idCatTypePayroll',$type);
								});
							}
						})
						->orderBy('fDate','DESC')
						->orderBy('folio','DESC')
						->paginate(10);
			
			return response(
				view('administracion.nomina.busqueda-revision',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 168,
						'requests'		=> $requests,
						'name'			=> $name, 
						'mindate'		=> $request->mindate,
						'maxdate'		=> $request->maxdate,
						'folio'			=> $folio,
						'fiscal'		=> $fiscal,
						'titleRequest'	=> $titleRequest,
						'department'	=> $department,
						'type' 			=> $type,
						'idEmployee'	=> $idEmployee
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(168), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showReview($id)
	{
		if (Auth::user()->module->where('id',168)->count()>0) 
		{
			$data = App\Module::find($this->module_id);
			$request = App\RequestModel::whereIn('status',[3])->find($id);

			if ($request != '') 
			{
				return view('administracion.nomina.revision',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 168,
						'request'	=> $request
					]
				);
			}
			else
			{
				return redirect('error');
			}
		}
	}

	public function updateReview(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$t_request				= App\RequestModel::find($id);

			if (in_array($t_request->status,[14,4,6])) 
			{
				$alert	= "swal('','".Lang::get("messages.request_already_ruled")."', 'error');";
				return redirect('administration/nomina')->with('alert',$alert);
			}

			if ($t_request->taxPayment == 1) 
			{
				if ($t_request->idDepartment == 11) 
				{
					$t_request->status	= 14;
				}
				else
				{
					$t_request->status	= 4;
				}
			}

			$t_request->idCheck		= Auth::user()->id;
			$t_request->reviewDate	= Carbon::now();
			$t_request->save();

			

			if ($t_request->taxPayment == 1) 
			{
				if ($t_request->idDepartment == 11) 
				{
					$emails = App\User::whereHas('module',function($q)
					{
						$q->where('id', 169);
					})
					->where('active',1)
					->where('notification',1)
					->get();
					$statusEmail = "Revisar";
				}
				else
				{
					$emails = App\User::whereHas('module',function($q)
					{
						$q->where('id', 170);
					})
					->where('active',1)
					->where('notification',1)
					->get();
					$statusEmail = "Autorizar";
				}
			}


			$user 	=  App\User::find($t_request->idRequest);
			if ($emails != "")
			{
				try
				{
					foreach ($emails as $email)
					{
						$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to 			= $email->email;
						$kind 			= "Nómina";
						$status 		= $statusEmail;
						$date 			= Carbon::now();
						$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
						$url 			= url('administration/nomina');
						$subject 		= "Solicitud por Revisar/Autorizar";
						//Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
					$alert	= "swal('','".Lang::get("messages.request_ruled")."', 'success');";
				}
				catch(\Exception $e)
				{
					$alert	= "swal('','".Lang::get("messages.request_ruled_no_mail")."', 'success');";
				}

			}
			return searchRedirect(168, $alert, 'administration/nomina');
		}
		else
		{
			return redirect('/error');
		}
	}

	public function constructionReviewSearch(Request $request)
	{
		if(Auth::user()->module->where('id',169)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$titleRequest	= $request->titleRequest;
			$name			= $request->name;
			$folio			= $request->folio;
			$department		= $request->department;
			$mindate		= $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate		= $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$fiscal			= $request->fiscal;
			$type 			= $request->type;
			$idEmployee 	= $request->idEmployee;

			$requests = App\RequestModel::where('kind',16)
						->where('status',14)
						->where(function ($query) use ($name, $folio, $titleRequest, $fiscal, $department,$mindate,$maxdate,$type,$idEmployee)
						{
							if($name != "")
							{
								$query->whereHas('requestUser',function($q) use($name)
								{
									$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
								});
							}
							if($folio != "")
							{
								$query->where('folio',$folio);
							}
							if($titleRequest != "")
							{
								$query->whereHas('nomina',function($q) use($titleRequest)
								{
									$q->where('title','LIKE','%'.preg_replace("/\s+/", "%", $titleRequest).'%');
								});
							}
							if($idEmployee != "")
							{
								$query->whereHas('nomina',function($q) use($idEmployee)
								{
									$q->whereHas('nominaEmployee',function($q) use($idEmployee)
									{
										$q->whereIn('idrealEmployee',$idEmployee);
									});
								});
							}
							if ($fiscal != "") 
							{
								$query->whereHas('nominasReal',function($q) use ($fiscal)
								{
									$q->whereIn('type_nomina',$fiscal);
								});
							}
							if ($department) 
							{
								$query->where('idDepartment',$department);
							}
							if($mindate != "" && $maxdate != "")
							{
								$query->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
							}
							if ($type != "") 
							{
								$query->whereHas('nominasReal',function($q) use ($type)
								{
									$q->where('idCatTypePayroll',$type);
								});
							}
						})
						->orderBy('fDate','DESC')
						->orderBy('folio','DESC')
						->paginate(10);
			
			return response(
				view('administracion.nomina.busqueda-revision-obra',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 169,
						'requests'		=> $requests,
						'name'			=> $name, 
						'mindate'		=> $request->mindate,
						'maxdate'		=> $request->maxdate,
						'folio'			=> $folio,
						'fiscal'		=> $fiscal,
						'titleRequest'	=> $titleRequest,
						'department'	=> $department,
						'type' 			=> $type,
						'idEmployee'	=> $idEmployee
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(169), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showConstructionReview($id)
	{
		if (Auth::user()->module->where('id',169)->count()>0) 
		{
			$data = App\Module::find($this->module_id);
			$request = App\RequestModel::where('status',14)->find($id);

			if ($request != '') 
			{
				return view('administracion.nomina.revision-obra',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 169,
						'request'	=> $request
					]
				);
			}
			else
			{
				return redirect('error');
			}
		}
	}

	public function updateConstructionReview(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$t_request							= App\RequestModel::find($id);

			if (in_array($t_request->status,[15,4,16])) 
			{
				$alert	= "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
				return redirect('administration/nomina')->with('alert',$alert);
			}

			if ($t_request->taxPayment == 1) 
			{
				if ($t_request->idDepartment == 11) 
				{
					$t_request->status	= 15;
				}
				else
				{
					$t_request->status	= 4;
				}
			}

			if ($t_request->taxPayment == 0) 
			{
				$t_request->status	= 15;
			}

			$t_request->idCheckConstruction		= Auth::user()->id;
			$t_request->reviewDateConstruction	= Carbon::now();
			$t_request->save();

			

			$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 170);
						})
						->where('active',1)
						->where('notification',1)
						->get();
			$statusEmail = "Autorizar";


			$user 	=  App\User::find($t_request->idRequest);
			if ($emails != "")
			{
				try
				{
					foreach ($emails as $email)
					{
						$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to 			= $email->email;
						$kind 			= "Nómina";
						$status 		= $statusEmail;
						$date 			= Carbon::now();
						$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
						$url 			= url('administration/nomina');
						$subject 		= "Solicitud por Autorizar";
						//Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
					$alert	= "swal('','".Lang::get("messages.request_ruled")."', 'success');";
				}
				catch(\Exception $e)
				{
					$alert	= "swal('','".Lang::get("messages.request_ruled_no_mail")."', 'success');";
				}
			}
			return searchRedirect(169, $alert, 'administration/nomina');
		}
		else
		{
			return redirect('/error');
		}
	}

	public function authorizationSearch(Request $request)
	{
		if(Auth::user()->module->where('id',170)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$titleRequest	= $request->titleRequest;
			$name			= $request->name;
			$folio			= $request->folio;
			$department		= $request->department;
			$mindate		= $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate		= $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$fiscal			= $request->fiscal;
			$type 			= $request->type;
			$idEmployee 	= $request->idEmployee;

			$requests = App\RequestModel::where('kind',16)
						->whereIn('status',[4,15])
						->where(function ($query) use ($name, $folio, $titleRequest, $fiscal, $department,$mindate,$maxdate,$type,$idEmployee)
						{
							if($name != "")
							{
								$query->whereHas('requestUser',function($q) use($name)
								{
									$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
								});
							}
							if($folio != "")
							{
								$query->where('folio',$folio);
							}
							if($titleRequest != "")
							{
								$query->whereHas('nomina',function($q) use($titleRequest)
								{
									$q->where('title','LIKE','%'.preg_replace("/\s+/", "%", $titleRequest).'%');
								});
							}
							if($idEmployee != "")
							{
								$query->whereHas('nomina',function($q) use($idEmployee)
								{
									$q->whereHas('nominaEmployee',function($q) use($idEmployee)
									{
										$q->whereIn('idrealEmployee',$idEmployee);
									});
								});
							}
							if ($fiscal != "") 
							{
								$query->whereHas('nominasReal',function($q) use ($fiscal)
								{
									$q->whereIn('type_nomina',$fiscal);
								});
							}
							if ($department) 
							{
								$query->where('idDepartment',$department);
							}
							if($mindate != "" && $maxdate != "")
							{
								$query->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
							}
							if ($type != "") 
							{
								$query->whereHas('nominasReal',function($q) use ($type)
								{
									$q->where('idCatTypePayroll',$type);
								});
							}
						})
						->orderBy('fDate','DESC')
						->orderBy('folio','DESC')
						->paginate(10);
			
			return response(
				view('administracion.nomina.busqueda-autorizacion',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 170,
						'requests'		=> $requests,
						'name'			=> $name, 
						'mindate'		=> $request->mindate,
						'maxdate'		=> $request->maxdate,
						'folio'			=> $folio,
						'fiscal'		=> $fiscal,
						'titleRequest'	=> $titleRequest,
						'department'	=> $department,
						'type' 			=> $type,
						'idEmployee'	=> $idEmployee
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(170), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showAuthorize($id)
	{
		if (Auth::user()->module->where('id',170)->count()>0) 
		{
			$data = App\Module::find($this->module_id);
			$request = App\RequestModel::whereIn('status',[4,15])->find($id);

			if ($request != '') 
			{
				return view('administracion.nomina.autorizacion',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 170,
						'request'	=> $request
					]
				);
			}
			else
			{
				return redirect('error');
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function updateAuthorize(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{

			$t_request	= App\RequestModel::find($id);

			if (in_array($t_request->status,[5,7])) 
			{
				$alert	= "swal('','".Lang::get("messages.request_already_ruled")."', 'error');";
				return redirect('administration/nomina')->with('alert',$alert);
			}

			if ($t_request->nominasReal->first()->idCatTypePayroll == '003' || $t_request->nominasReal->first()->idCatTypePayroll == '004' ) 
			{
				$nomina_employee = App\NominaEmployee::where('idnomina',$t_request->nominasReal->first()->idnomina)->get();
				foreach ($nomina_employee as $emp) 
				{
					foreach (App\WorkerData::where('idEmployee',$emp->idrealEmployee)->where('visible',1)->get() as $w) 
					{
						$update				= App\WorkerData::find($w->id);
						$update->visible	= 0;
						$update->save();
					}
					$workingData				= App\WorkerData::find($emp->idworkingData);
					$new_data					= $workingData->replicate();
					$new_data->visible			= 1;
					$new_data->workerStatus		= 2;
					$new_data->downDate 		= $emp->down_date;
					$new_data->status_reason	= 'SE AUTORIZO UN FINIQUITO EN LA SOLICITUD DE NÓMINA CON FOLIO #'.$id;
					$new_data->push();
				}
			}

			$t_request->status			= 5;
			$t_request->idAuthorize		= Auth::user()->id;
			$t_request->authorizeDate	= Carbon::now();
			$t_request->save();
			
			if($t_request->nominasReal->first()->type_nomina == 3)
			{	
				$idNomina		= $t_request->folio;
				$enterpriseId	= "";
				$projectId		= "";

				if ($t_request->prenominaData()->exists()) 
				{
					if ($t_request->prenominaData->employeeRegisterData()->exists()) 
					{
						$enterpriseId = $t_request->prenominaData->employeeRegisterData->enterprise_id;
					}
					else
					{
						foreach ($t_request->nominasReal->first()->nominaEmployee as $employee) 
						{
							if ($employee->getWorkerData()->exists()) 
							{
								if ($employee->getWorkerData->employeeRegisterData()->exists()) 
								{
									$enterpriseId = $employee->getWorkerData->employeeRegisterData->enterprise_id;
									break;
								}
							}
						}
					}
					$projectId = $t_request->prenominaData->project_id;
				}
				else
				{
					foreach ($t_request->nominasReal->first()->nominaEmployee as $employee) 
					{
						if ($employee->getWorkerData()->exists()) 
						{
							if ($employee->getWorkerData->employeeRegisterData()->exists()) 
							{
								$enterpriseId = $employee->getWorkerData->employeeRegisterData->enterprise_id;
								break;
							}
						}
					}
				}

				$iva											= App\Parameter::where('parameter_name','IVA')->first()->parameter_value/100;
				$totalNomina									= $t_request->nominasReal->first()->amount;
				if ($projectId != "" && $projectId == 126)
				{
					$percentage = 0.1;
				} 
				else
				{
					$percentage = 0.08;
				}
				$totalNominaAd									= round($totalNomina*$percentage,2);
				$ivaCalc										= round($totalNominaAd*$iva,2);
				$t_request->nominasReal->first()->amount_nom35	= $totalNominaAd+$ivaCalc;
				$t_request->nominasReal->first()->save();

				$new_request	= new App\RequestModel();
				$id				= $this->saveRequestNom($totalNomina,$totalNominaAd,$ivaCalc,$new_request,$idNomina,$enterpriseId,$projectId);
			}

			$emailRequest 	= App\User::where('id',$t_request->idElaborate)
							->orWhere('id',$t_request->idRequest)
							->where('notification',1)
							->get();


			if ($emailRequest != "")
			{
				try
				{
					foreach ($emailRequest as $email)
					{
						$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to 			= $email->email;
						$kind 			= "Nómina";
						$status 		= "AUTORIZADA";
						$date 			= Carbon::now();
						$url 			= url('administration/nomina');
						$subject 		= "Estado de Solicitud";
						$requestUser	= null;
						//Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
					$alert	= "swal('','".Lang::get("messages.request_ruled")."', 'success');";
				}
				catch(\Exception $e)
				{
					$alert	= "swal('','".Lang::get("messages.request_ruled_no_mail")."', 'success');";
				}
			}
			return searchRedirect(170, $alert, 'administration/nomina');
		}
		else
		{
			return redirect('/error');
		}
	}

	static function saveRequestNom($totalNomina,$totalNominaAd,$ivaCalc,$new_request,$idNomina,$enterpriseId,$projectId)
	{
		$accountId			= "";
		$providerDataId		= "";
		$providerId			= "";
		$providerAccount	= "";

		//Novelty - Nelti Gestiones
		//Proyecta - Mille Bolle Asociados
		if ($enterpriseId != "") 
		{
			switch ($enterpriseId) 
			{
				case 5:
					$accountId			= 1637;
					$providerDataId		= 450;
					$providerId			= 4607;
					$providerAccount	= 1183;
					break;

				case 9:
					$accountId			= 3878;
					$providerDataId		= 451;
					$providerId			= 822;
					$providerAccount	= 1157;
					break;
				
				default:
					// code...
					break;
			}
		}

		$new_request->taxPayment 	= 1;
		$new_request->kind			= 1;
		$new_request->fDate			= Carbon::now();
		$new_request->status		= 2;
		$new_request->idRequest		= 17;
		$new_request->idElaborate	= 18;
		$new_request->idProject 	= $projectId;
		$new_request->account 		= $accountId;
		$new_request->idEnterprise 	= $enterpriseId;
		$new_request->idArea 		= 1;
		$new_request->idDepartment 	= 4;
		$new_request->idNomina 		= $idNomina;
		$new_request->save();

		$enterpriseName = $enterpriseId != "" ? strtoupper(App\Enterprise::find($enterpriseId)->name) : "";

		$t_purchase							= new App\Purchase();
		$t_purchase->title 					= 'ASESORAMIENTO DE RECURSOS HUMANOS (NOMINA '.$idNomina .') '.$enterpriseName;
		$t_purchase->datetitle 				= Carbon::now();
		$t_purchase->idFolio				= $new_request->folio;
		$t_purchase->idKind					= $new_request->kind;
		$t_purchase->idProvider				= $providerId;
		$t_purchase->provider_data_id 		= $providerDataId;
		$t_purchase->provider_has_banks_id 	= $providerAccount;
		$t_purchase->typeCurrency 			= 'MXN';
		$t_purchase->subtotales	 			= $totalNomina+$totalNominaAd;
		$t_purchase->tax 					= $ivaCalc;
		$t_purchase->amount 				= $totalNomina+$totalNominaAd+$ivaCalc;
		$t_purchase->paymentMode 			= 'Transferencia';
		$t_purchase->save();

		$idPurchase = $t_purchase->idPurchase;		

		$t_detailPurchase				= new App\DetailPurchase();
		$t_detailPurchase->idPurchase	= $idPurchase;
		$t_detailPurchase->quantity		= 1;
		$t_detailPurchase->unit			= 'servicio';
		$t_detailPurchase->description 	= 'Servicios de evaluación y valoración de salud individual';
		$t_detailPurchase->unitPrice	= $totalNomina;
		$t_detailPurchase->tax			= 0;
		$t_detailPurchase->discount		= 0;
		$t_detailPurchase->amount		= $totalNomina;
		$t_detailPurchase->typeTax		= 'no';
		$t_detailPurchase->subtotal		= $totalNomina;
		$t_detailPurchase->save();

		$t_detailPurchase				= new App\DetailPurchase();
		$t_detailPurchase->idPurchase	= $idPurchase;
		$t_detailPurchase->quantity		= 1;
		$t_detailPurchase->unit			= 'servicio';
		$t_detailPurchase->description 	= 'Comisión por administración';
		$t_detailPurchase->unitPrice	= $totalNominaAd;
		$t_detailPurchase->tax			= $ivaCalc;
		$t_detailPurchase->discount		= 0;
		$t_detailPurchase->amount		= $totalNominaAd+$ivaCalc;
		$t_detailPurchase->typeTax		= 'a';
		$t_detailPurchase->subtotal		= $totalNominaAd;
		$t_detailPurchase->save();

		return $new_request->folio;
	}

	public function exportReviewNF($id)
	{
		if(Auth::user()->module->whereIn('id',[166,167,168])->count()>0)
		{
			Excel::create('Revisión de Nominas', function($excel) use ($id)
			{
				$excel->sheet('No fiscal',function($sheet) use ($id)
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
					$sheet->setColumnFormat(array(
						'K' => '@',
						'L' => '@',
						'M' => '@',
					));
					$sheet->mergeCells('A1:Z1');
					$sheet->mergeCells('A2:G2');
					$sheet->mergeCells('H2:N2');
					$sheet->mergeCells('O2:Z2');
					$sheet->cell('A1:Z1', function($cells)
					{
						$cells->setBackground('#343a40');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A2:G2', function($cells)
					{
						$cells->setBackground('#f8cd5c');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('H2:N2', function($cells)
					{
						$cells->setBackground('#7fc544');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('O2:Z2', function($cells)
					{
						$cells->setBackground('#EE881F');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('Y', function($cells)
					{
						$cells->setBackground('#fca700');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('Z', function($cells)
					{
						$cells->setBackground('#fca700');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A1:Z3', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});
					$sheet->row(1,['nomina']);
					$sheet->row(2,['informacion_general','','','','','','','datos_del_pago','','','','','','','datos_complemento']);
					$sheet->row(3,[
							'id',
							'empleado',
							'proyecto', // N
							'empresa', // O
							'sdi', // AB
							'periodicidad', // AC
							'complemento', // AE
							'forma_de_pago',// AL
							'alias', //AM
							'banco', // AN
							'clabe', // AO
							'cuenta', // AP
							'tarjeta', // AQ
							'sucursal', // AR
							'referencia',
							'razon_de_pago', //AT
							'descuento', //AV
							'extra', //AV
							'sueldo_neto_fiscal',
							'retencion_infonavit',
							'retencion_fonacot',
							'horas_extra',
							'dias_festivos',
							'domingos_trabajados',
							'sueldo_neto_no_fiscal', //AU
							'sueldo_neto',
						]);

					$req	= App\RequestModel::find($id);

					$rf = App\RequestModel::where('kind',16)
						->where('idprenomina',$req->idprenomina)
						->where('idDepartment',$req->idDepartment)
						->first();
						
					$beginMerge	= 2;
					foreach (App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$req->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $nomina)
					{
						$nominaemp = App\NominaEmployee::where('idrealEmployee',$nomina->idrealEmployee)
									->where('idnomina',$rf->nominasReal->first()->idnomina)
									->first();
						$tempCount	= 0;
						$row	= [];
						$row[]	= $nomina->idrealEmployee;
						$row[]	= $nomina->employee->first()->last_name.' '.$nomina->employee->first()->scnd_last_name.' '.$nomina->employee->first()->name;
						$row[]	= $nomina->workerData->first()->projects()->exists() ? $nomina->workerData->first()->projects->proyectName :'Sin proyecto';
						$row[]	= $nomina->workerData->first()->enterprises()->exists() ? $nomina->workerData->first()->enterprises->name : '';
						
						$row[] = $nomina->workerData->first()->sdi;
						$row[] = $nomina->workerData->first()->periodicity!='' ? App\CatPeriodicity::where('c_periodicity',$nomina->workerData->first()->periodicity)->first()->description : '';
						$row[] = $nomina->workerData->first()->complement;
						$row[] = $nomina->nominasEmployeeNF->first()->paymentMethod()->exists() ? $nomina->nominasEmployeeNF->first()->paymentMethod->method : '';

						if ($nomina->nominasEmployeeNF->first()->idemployeeAccounts != '') 
						{
							foreach (App\EmployeeAccount::where('id',$nomina->nominasEmployeeNF->first()->idemployeeAccounts)->get() as $b) 
							{
								$row[]	= $b->alias;
								$row[]	= $b->bank()->exists() ? $b->bank->description : '';
								$row[]	= $b->clabe.' ';
								$row[]	= $b->account.' ';
								$row[]	= $b->cardNumber.' ';
								$row[]	= $b->branch.' ';
							}
						}
						else
						{
							$row[]	= '';
							$row[]	= '';
							$row[]	= '';
							$row[]	= '';
							$row[]	= '';
							$row[]	= '';
						}
						$row[] = $nomina->nominasEmployeeNF->first()->reference;
						$row[] = $nomina->nominasEmployeeNF->first()->reasonAmount;

						$total_discounts	= 0;
						$total_extras		= 0;

						if ($nomina->nominasEmployeeNF->first()->discounts()->exists()) 
						{
							$total_discounts = $nomina->nominasEmployeeNF->first()->discounts->sum('amount');
						}
						else
						{
							$total_discounts = $nomina->nominasEmployeeNF->first()->discount != '' ? $nomina->nominasEmployeeNF->first()->discount : 0;
						}
						
						if ($nomina->nominasEmployeeNF->first()->extras()->exists()) 
						{
							$total_extras = $nomina->nominasEmployeeNF->first()->extras->sum('amount');
						}
						else
						{
							$total_extras = 0;
						}

						$row[] = $total_discounts;
						$row[] = $total_extras; 

						if ($nominaemp != '' || $nominaemp != null) 
						{
							if ($nominaemp->salary()->exists()) 
							{
								$row[] = $nominaemp->salary->first()->netIncome;
								$row[] = $nominaemp->salary->first()->infonavit != '' ? round($nominaemp->salary->first()->infonavit,2) : 0;
								$row[] = $nominaemp->salary->first()->fonacot != '' ? round($nominaemp->salary->first()->fonacot,2) : 0;
							}
							elseif ($nominaemp->bonus()->exists()) {
								$row[] = $nominaemp->bonus->first()->netIncome;
								$row[] = 0;
								$row[] = 0;
							}
							elseif ($nominaemp->liquidation()->exists()) 
							{
								$row[] = $nominaemp->liquidation->first()->netIncome;
								$row[] = 0;
								$row[] = 0;
							}
							elseif ($nominaemp->vacationPremium()->exists()) 
							{
								$row[] = $nominaemp->vacationPremium->first()->netIncome;
								$row[] = 0;
								$row[] = 0;
							}
							elseif ($nominaemp->profitSharing()->exists()) 
							{
								$row[] = $nominaemp->profitSharing->first()->netIncome;
								$row[] = 0;
								$row[] = 0;
							}
							else
							{
								$row[] = 0;
								$row[] = 0;
								$row[] = 0;
							}
						}
						else
						{
							$row[] = 0;
							$row[] = 0;
							$row[] = 0;
						}
						$row[] = $nomina->nominasEmployeeNF->first()->extra_time;
						$row[] = $nomina->nominasEmployeeNF->first()->holiday;
						$row[] = $nomina->nominasEmployeeNF->first()->sundays;
						$row[] = $nomina->nominasEmployeeNF->first()->amount;
						$row[] = $nomina->nominasEmployeeNF->first()->netIncome;
						$sheet->appendRow($row);
					}
				});
			})->export('xlsx');
		}
		else
		{
			return redirect('/');
		}
	}

	public function exportConstructionReviewNF($id)
	{
		if(Auth::user()->module->whereIn('id',[166,167,168,169,170])->count()>0)
		{
			$flagSalary = false;
			$requestModel = App\RequestModel::find($id);
			if ($requestModel->nominasReal->first()->idCatTypePayroll == "001") 
			{
				$flagSalary = true;
			}
			else
			{
				$flagSalary = false;
			}

			if ($flagSalary) 
			{		
				$selectRaw 	= '
								nomina_employees.idrealEmployee as idrealEmployee,
								CONCAT_WS(" ", real_employees.last_name, real_employees.scnd_last_name, real_employees.name) as name,
								real_employees.curp as curp,
								projects.proyectName as projectName,
								enterprises.name as enterpriseName,
								worker_datas.sdi as sdi,
								IF(nomina_employees.idCatPeriodicity IS NOT NULL, nomina_emp_periodicity.description,
									IF(worker_datas.periodicity IS NOT NULL, worker_data_periodicity.description,
										IF(nominas.idCatPeriodicity IS NOT NULL, nominas_periodicity.description, "")
									)
								) as periodicity,
								worker_datas.netIncome as netIncome,
								worker_datas.complement as complement,
								payment_methods.method as paymentMethod,
								employee_accounts.alias as alias,
								cat_banks.description as bank,
								employee_accounts.clabe as clabe,
								employee_accounts.account as account,
								employee_accounts.cardNumber as cardNumber,
								employee_accounts.branch as branch,
								IF(nomina_employees.idCatPeriodicity IS NOT NULL, (nomina_emp_periodicity.days - IFNULL(nomina_employees.absence,0)),
									IF(worker_datas.periodicity IS NOT NULL, (worker_data_periodicity.days - IFNULL(nomina_employees.absence,0)),
										IF(nominas.idCatPeriodicity IS NOT NULL, (nominas_periodicity.days - IFNULL(nomina_employees.absence,0)), "")
									)
								) as workedDays,
								IFNULL(nomina_employees.absence,0) as absence,
								IFNULL(nomina_employees.extra_hours,0) as extra_hours,
								IFNULL(nomina_employees.holidays,0) as holidays,
								IFNULL(nomina_employees.sundays,0) as sundays,
								nomina_employee_n_fs.reference as reference,
								nomina_employee_n_fs.reasonAmount as reasonAmount,
								IFNULL(discounts_nominas.discounts, 0) as discounts,
								IFNULL(extras_nominas.extras, 0) as extras,
								ROUND(IF(nominas.idCatTypePayroll = "001" AND nomina_employees_fiscal.fiscal = 1, IFNULL(salaries.infonavit,0), 0),2) AS infonavit_fiscal,
								ROUND(IF(nominas.idCatTypePayroll = "001" AND nomina_employees_fiscal.fiscal = 1, IFNULL(salaries.fonacot,0), 0),2) AS fonacot_fiscal,
								ROUND(IF(nominas.idCatTypePayroll = "001" AND nomina_employees_fiscal.fiscal = 1, IFNULL(salaries.loan_retention,0), 0),2) AS loan_retention_fiscal,
								ROUND(
									IF(nominas.idCatTypePayroll = "001" AND nomina_employees_fiscal.fiscal = 1, IFNULL(salaries.netIncome,0), 
										IF(nominas.idCatTypePayroll = "002" AND nomina_employees_fiscal.fiscal = 1, IFNULL(bonuses.netIncome,0), 
											IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees_fiscal.fiscal = 1, IFNULL(liquidations.netIncome,0), 
												IF(nominas.idCatTypePayroll = "005" AND nomina_employees_fiscal.fiscal = 1, IFNULL(vacation_premia.netIncome,0), 
													IF(nominas.idCatTypePayroll = "006" AND nomina_employees_fiscal.fiscal = 1, IFNULL(profit_sharings.netIncome,0), 0
													)
												)
											)
										)
								),2) AS netIncome_fiscal,
								ROUND(IFNULL(discounts_nominas_infonavit.discounts, 0),2) as discounts_infonavit,
								ROUND(IFNULL(discounts_nominas_fonacot.discounts, 0),2) as discounts_fonacot,
								nomina_employee_n_fs.extra_time as extra_time_nf,
								nomina_employee_n_fs.holiday as holiday_nf,
								nomina_employee_n_fs.sundays as sundays_nf,
								nomina_employee_n_fs.complementPartial as complementPartial_nf,
								nomina_employee_n_fs.amount as amount_nf,
								(ROUND(
									IF(nominas.idCatTypePayroll = "001" AND nomina_employees_fiscal.fiscal = 1, IFNULL(salaries.netIncome,0), 
										IF(nominas.idCatTypePayroll = "002" AND nomina_employees_fiscal.fiscal = 1, IFNULL(bonuses.netIncome,0), 
											IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees_fiscal.fiscal = 1, IFNULL(liquidations.netIncome,0), 
												IF(nominas.idCatTypePayroll = "005" AND nomina_employees_fiscal.fiscal = 1, IFNULL(vacation_premia.netIncome,0), 
													IF(nominas.idCatTypePayroll = "006" AND nomina_employees_fiscal.fiscal = 1, IFNULL(profit_sharings.netIncome,0), 0
													)
												)
											)
										)
								),2) 
									+ ROUND(IF(nominas.idCatTypePayroll = "001" AND nomina_employees_fiscal.fiscal = 1, IFNULL(salaries.infonavit,0), 0),2) 
									+ ROUND(IF(nominas.idCatTypePayroll = "001" AND nomina_employees_fiscal.fiscal = 1, IFNULL(salaries.fonacot,0), 0),2) 
									+ ROUND(IF(nominas.idCatTypePayroll = "001" AND nomina_employees_fiscal.fiscal = 1, IFNULL(salaries.loan_retention,0), 0),2) 
									+ ROUND(nomina_employee_n_fs.amount,2)) as neto_total
							';
			}
			else
			{
				$selectRaw 	= '
								nomina_employees.idrealEmployee as idrealEmployee,
								CONCAT_WS(" ", real_employees.last_name, real_employees.scnd_last_name, real_employees.name) as name,
								real_employees.curp as curp,
								projects.proyectName as projectName,
								enterprises.name as enterpriseName,
								worker_datas.sdi as sdi,
								IF(nomina_employees.idCatPeriodicity IS NOT NULL, nomina_emp_periodicity.description,
									IF(worker_datas.periodicity IS NOT NULL, worker_data_periodicity.description,
										IF(nominas.idCatPeriodicity IS NOT NULL, nominas_periodicity.description, "")
									)
								) as periodicity,
								worker_datas.netIncome as netIncome,
								worker_datas.complement as complement,
								payment_methods.method as paymentMethod,
								employee_accounts.alias as alias,
								cat_banks.description as bank,
								employee_accounts.clabe as clabe,
								employee_accounts.account as account,
								employee_accounts.cardNumber as cardNumber,
								employee_accounts.branch as branch,
								
								nomina_employee_n_fs.reference as reference,
								nomina_employee_n_fs.reasonAmount as reasonAmount,
								ROUND(
									IF(nominas.idCatTypePayroll = "001" AND nomina_employees_fiscal.fiscal = 1, IFNULL(salaries.netIncome,0), 
										IF(nominas.idCatTypePayroll = "002" AND nomina_employees_fiscal.fiscal = 1, IFNULL(bonuses.netIncome,0), 
											IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees_fiscal.fiscal = 1, IFNULL(liquidations.netIncome,0), 
												IF(nominas.idCatTypePayroll = "005" AND nomina_employees_fiscal.fiscal = 1, IFNULL(vacation_premia.netIncome,0), 
													IF(nominas.idCatTypePayroll = "006" AND nomina_employees_fiscal.fiscal = 1, IFNULL(profit_sharings.netIncome,0), 0
													)
												)
											)
										)
								),2) AS netIncome_fiscal,
								IFNULL(discounts_nominas.discounts, 0) as discounts,
								IFNULL(extras_nominas.extras, 0) as extras,
								nomina_employee_n_fs.amount as amount_nf,
								(ROUND(
									IF(nominas.idCatTypePayroll = "001" AND nomina_employees_fiscal.fiscal = 1, IFNULL(salaries.netIncome,0), 
										IF(nominas.idCatTypePayroll = "002" AND nomina_employees_fiscal.fiscal = 1, IFNULL(bonuses.netIncome,0), 
											IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees_fiscal.fiscal = 1, IFNULL(liquidations.netIncome,0), 
												IF(nominas.idCatTypePayroll = "005" AND nomina_employees_fiscal.fiscal = 1, IFNULL(vacation_premia.netIncome,0), 
													IF(nominas.idCatTypePayroll = "006" AND nomina_employees_fiscal.fiscal = 1, IFNULL(profit_sharings.netIncome,0), 0
													)
												)
											)
										)
								),2) + ROUND(nomina_employee_n_fs.amount,2)) as neto_total
							';
			}
			$nominaEmployees = DB::table('request_models')
								->selectRaw($selectRaw)
								->leftJoin('nominas','nominas.idFolio','request_models.folio')
								->leftJoin('cat_periodicities as nominas_periodicity','nominas_periodicity.c_periodicity','nominas.idCatPeriodicity')
								->leftJoin('nomina_employees','nomina_employees.idnomina','nominas.idnomina')
								->leftJoin('cat_periodicities as nomina_emp_periodicity','nomina_emp_periodicity.c_periodicity','nomina_employees.idCatPeriodicity')
								->leftJoin('real_employees','real_employees.id','nomina_employees.idrealEmployee')
								->leftJoin('worker_datas','worker_datas.id','nomina_employees.idWorkingData')
								->leftJoin('enterprises','enterprises.id','worker_datas.enterprise')
								->leftJoin('projects','projects.idproyect','worker_datas.project')
								->leftJoin('cat_periodicities as worker_data_periodicity','worker_data_periodicity.c_periodicity','worker_datas.periodicity')
								->leftJoin('nomina_employee_n_fs','nomina_employee_n_fs.idnominaEmployee','nomina_employees.idnominaEmployee')
								->leftJoin('payment_methods','payment_methods.idpaymentMethod','nomina_employee_n_fs.idpaymentMethod')
								->leftJoin('employee_accounts','employee_accounts.id','nomina_employee_n_fs.idemployeeAccounts')
								->leftJoin('cat_banks','cat_banks.c_bank','employee_accounts.idCatBank')
								->leftJoin(DB::raw('(SELECT idnominaemployeenf, SUM(amount) AS discounts FROM discounts_nominas  WHERE reason NOT LIKE "%INFONAVIT%" AND reason NOT LIKE "%FONACOT%" GROUP BY idnominaemployeenf) as discounts_nominas'),'discounts_nominas.idnominaemployeenf','nomina_employee_n_fs.idnominaemployeenf')
								->leftJoin(DB::raw('(SELECT idnominaemployeenf, SUM(amount) AS discounts FROM discounts_nominas  WHERE reason LIKE "%INFONAVIT%" GROUP BY idnominaemployeenf) as discounts_nominas_infonavit'),'discounts_nominas_infonavit.idnominaemployeenf','nomina_employee_n_fs.idnominaemployeenf')
								->leftJoin(DB::raw('(SELECT idnominaemployeenf, SUM(amount) AS discounts FROM discounts_nominas  WHERE reason LIKE "%FONACOT%" GROUP BY idnominaemployeenf) as discounts_nominas_fonacot'),'discounts_nominas_fonacot.idnominaemployeenf','nomina_employee_n_fs.idnominaemployeenf')
								->leftJoin(DB::raw('(SELECT idnominaemployeenf, SUM(amount) AS extras FROM extras_nominas GROUP BY idnominaemployeenf) as extras_nominas'),'extras_nominas.idnominaemployeenf','nomina_employee_n_fs.idnominaemployeenf')
								->leftJoin('request_models as request_fiscal', function($join)
								{
									$join->on('request_fiscal.idprenomina','request_models.idprenomina')
										->on('request_fiscal.idDepartment','request_models.idDepartment')
										->where('request_fiscal.taxPayment',1)
										->where('request_fiscal.kind',16);
								})
								->leftJoin('nominas as nominas_fiscal','nominas_fiscal.idFolio','request_fiscal.folio')
								->leftJoin('nomina_employees as nomina_employees_fiscal',function($join)
								{
									$join->on('nomina_employees_fiscal.idrealEmployee','nomina_employees.idrealEmployee')
										->on('nomina_employees_fiscal.idnomina','nominas_fiscal.idnomina');
								})
								->leftJoin('salaries','salaries.idnominaEmployee','nomina_employees_fiscal.idnominaEmployee')
								->leftJoin('bonuses','bonuses.idnominaEmployee','nomina_employees_fiscal.idnominaEmployee')
								->leftJoin('settlements','settlements.idnominaEmployee','nomina_employees_fiscal.idnominaEmployee')
								->leftJoin('liquidations','liquidations.idnominaEmployee','nomina_employees_fiscal.idnominaEmployee')
								->leftJoin('vacation_premia','vacation_premia.idnominaEmployee','nomina_employees_fiscal.idnominaEmployee')
								->leftJoin('profit_sharings','profit_sharings.idnominaEmployee','nomina_employees_fiscal.idnominaEmployee')
								->where('request_models.folio',$id)
								->where('nomina_employees.visible',1)
								->orderBy('real_employees.last_name','ASC')
								->orderBy('real_employees.scnd_last_name','ASC')
								->orderBy('real_employees.name','ASC')
								->get();
			$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat	= (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('343a40')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('f8cd5c')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('7fc544')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol4	= (new StyleBuilder())->setBackgroundColor('EE881F')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol5	= (new StyleBuilder())->setBackgroundColor('ffffff')->setFontColor(Color::BLACK)->setFontBold()->build();
			$mhStyleCol6	= (new StyleBuilder())->setBackgroundColor('fca700')->setFontColor(Color::BLACK)->setFontBold()->build();
			$writer			= WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('revisión-de-nómina.xlsx');
			if ($flagSalary) 
			{
				$headerArray	= ['nomina','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
			}
			else
			{
				$headerArray	= ['nomina','','','','','','','','','','','','','','','','','','','','','',''];
			}
			$tempHeaders		= [];
			foreach($headerArray as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			if ($flagSalary) 
			{
				$headerArray	= ['informacion_general','','','','','','','','','datos_de_forma_pago','','','','','','','datos_del_pago','','','','','','','','','','','','','','','','','','','',''];
			}
			else
			{
				$headerArray	= ['informacion_general','','','','','','','','','datos_de_forma_pago','','','','','','','datos_del_pago','','','','','',''];
			}
			$tempHeaders		= [];
			foreach($headerArray as $k => $header)
			{
				if($k <= 8)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
				}
				elseif($k <= 15)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
				}
				else
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			if ($flagSalary) 
			{
				$subHeaderArray	= ['id','empleado','curp','proyecto', 'empresa','sdi', 'periodicidad', 'sueldo_neto','complemento', 'forma_de_pago','alias','banco','clabe','cuenta','tarjeta','sucursal','dias_trabajados','faltas','horas_extra','dias_festivos','domingos_trabajados','referencia','razon_de_pago','descuento','extra','infonavit_fiscal','fonacot_fiscal','prestamo_fiscal','sueldo_neto_fiscal','retencion_infonavit','retencion_fonacot','total_horas_extra','total_dias_festivos','total_domingos_trabajados','sueldo_neto_no_fiscal','total_no_fiscal_por_pagar','neto_total'];
			}
			else
			{
				$subHeaderArray	= ['id','empleado','curp','proyecto','empresa','sdi','periodicidad','sueldo_neto','complemento', 'forma_de_pago','alias','banco', 'clabe', 'cuenta', 'tarjeta', 'sucursal', 'referencia','razon_de_pago', 'total_fiscal_pagado','descuento', 'extra', 'total_no_fiscal_por_pagar','neto_total'];
			}
			$tempHeaders	= [];
			foreach($subHeaderArray as $k => $subHeader)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol5);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			$tempIdrealEmployee     = '';
			$kindRow       = true;
			foreach($nominaEmployees as $nomina_employee)
			{
				if($tempIdrealEmployee != $nomina_employee->idrealEmployee)
				{
					$tempIdrealEmployee = $nomina_employee->idrealEmployee;
					$kindRow = !$kindRow;
					
				}
				$tmpArr = [];
				foreach($nomina_employee as $k => $r)
				{
					if(in_array($k,['discounts','extras','infonavit_fiscal','fonacot_fiscal','loan_retention_fiscal','netIncome_fiscal','discounts_infonavit','discounts_fonacot','extra_time_nf','holiday_nf','sundays_nf','complementPartial_nf','neto_total']))
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}
					elseif($k == 'amount_nf')
					{
						$tmpArr[] = WriterEntityFactory::createCell((double)$r,$mhStyleCol6);
					}
					else
					{
						$tmpArr[] = WriterEntityFactory::createCell($r);
					}
				}
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr);
				}
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
		}
		else
		{
			return redirect('/');
		}
	}

	public function exportAuthorizeNF($id)
	{
		if(Auth::user()->module->where('id',170)->count()>0)
		{
			Excel::create('Revisión de Nominas', function($excel) use ($id)
			{
				$excel->sheet('No fiscal',function($sheet) use ($id)
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
					$sheet->setColumnFormat(array(
						'K' => '@',
						'L' => '@',
						'M' => '@',
					));

					$sheet->mergeCells('A1:Z1');
					$sheet->mergeCells('A2:G2');
					$sheet->mergeCells('H2:N2');
					$sheet->mergeCells('O2:Z2');
					$sheet->cell('A1:Z1', function($cells)
					{
						$cells->setBackground('#343a40');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A2:G2', function($cells)
					{
						$cells->setBackground('#f8cd5c');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('H2:N2', function($cells)
					{
						$cells->setBackground('#7fc544');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('O2:Z2', function($cells)
					{
						$cells->setBackground('#EE881F');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('Y', function($cells)
					{
						$cells->setBackground('#fca700');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('Z', function($cells)
					{
						$cells->setBackground('#fca700');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A1:Z3', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});
					$sheet->row(1,['nomina']);
					$sheet->row(2,['informacion_general','','','','','','','datos_del_pago','','','','','','','datos_complemento']);
					$sheet->row(3,[
							'id',
							'empleado',
							'proyecto', // N
							'empresa', // O
							'sdi', // AB
							'periodicidad', // AC
							'complemento', // AE
							'forma_de_pago',// AL
							'alias', //AM
							'banco', // AN
							'clabe', // AO
							'cuenta', // AP
							'tarjeta', // AQ
							'sucursal', // AR
							'referencia',
							'razon_de_pago', //AT
							'descuento', //AV
							'extra', //AV
							'sueldo_neto_fiscal',
							'retencion_infonavit',
							'retencion_fonacot',
							'horas_extra',
							'dias_festivos',
							'domingos_trabajados',
							'sueldo_neto_no_fiscal', //AU
							'sueldo_neto',
						]);

					$req	= App\RequestModel::find($id);

					$rf = App\RequestModel::where('kind',16)
						->where('idprenomina',$req->idprenomina)
						->where('idDepartment',$req->idDepartment)
						->first();
						
					$beginMerge	= 2;
					foreach (App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$req->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $nomina)
					{
						$nominaemp = App\NominaEmployee::where('idrealEmployee',$nomina->idrealEmployee)
									->where('idnomina',$rf->nominasReal->first()->idnomina)
									->first();
						$tempCount	= 0;
						$row	= [];
						$row[]	= $nomina->idrealEmployee;
						$row[]	= $nomina->employee->first()->last_name.' '.$nomina->employee->first()->scnd_last_name.' '.$nomina->employee->first()->name;
						$row[]	= $nomina->workerData->first()->projects()->exists() ? $nomina->workerData->first()->projects->proyectName :'Sin proyecto';
						$row[]	= $nomina->workerData->first()->enterprises()->exists() ? $nomina->workerData->first()->enterprises->name : '';
						
						$row[] = $nomina->workerData->first()->sdi;
						$row[] = $nomina->workerData->first()->periodicity!='' ? App\CatPeriodicity::where('c_periodicity',$nomina->workerData->first()->periodicity)->first()->description : '';
						$row[] = $nomina->workerData->first()->complement;
						$row[] = $nomina->nominasEmployeeNF->first()->paymentMethod()->exists() ? $nomina->nominasEmployeeNF->first()->paymentMethod->method : '';

						if ($nomina->nominasEmployeeNF->first()->idemployeeAccounts != '') 
						{
							foreach (App\EmployeeAccount::where('id',$nomina->nominasEmployeeNF->first()->idemployeeAccounts)->get() as $b) 
							{
								$row[]	= $b->alias;
								$row[]	= $b->bank()->exists() ? $b->bank->description : '';
								$row[]	= $b->clabe.' ';
								$row[]	= $b->account.' ';
								$row[]	= $b->cardNumber.' ';
								$row[]	= $b->branch.' ';
							}
						}
						else
						{
							$row[]	= '';
							$row[]	= '';
							$row[]	= '';
							$row[]	= '';
							$row[]	= '';
							$row[]	= '';
						}
						$row[] = $nomina->nominasEmployeeNF->first()->reference;
						$row[] = $nomina->nominasEmployeeNF->first()->reasonAmount;

						$total_discounts	= 0;
						$total_extras		= 0;

						if ($nomina->nominasEmployeeNF->first()->discounts()->exists()) 
						{
							$total_discounts = $nomina->nominasEmployeeNF->first()->discounts->sum('amount');
						}
						else
						{
							$total_discounts = $nomina->nominasEmployeeNF->first()->discount != '' ? $nomina->nominasEmployeeNF->first()->discount : 0;
						}
						
						if ($nomina->nominasEmployeeNF->first()->extras()->exists()) 
						{
							$total_extras = $nomina->nominasEmployeeNF->first()->extras->sum('amount');
						}
						else
						{
							$total_extras = 0;
						}

						$row[] = $total_discounts;
						$row[] = $total_extras; 

						if ($nominaemp != '' || $nominaemp != null) 
						{
							if ($nominaemp->salary()->exists()) 
							{
								$row[] = $nominaemp->salary->first()->netIncome;
								$row[] = $nominaemp->salary->first()->infonavit != '' ? round($nominaemp->salary->first()->infonavit,2) : 0;
								$row[] = $nominaemp->salary->first()->fonacot != '' ? round($nominaemp->salary->first()->fonacot,2) : 0;
							}
							elseif ($nominaemp->bonus()->exists()) {
								$row[] = $nominaemp->bonus->first()->netIncome;
								$row[] = 0;
								$row[] = 0;
							}
							elseif ($nominaemp->liquidation()->exists()) 
							{
								$row[] = $nominaemp->liquidation->first()->netIncome;
								$row[] = 0;
								$row[] = 0;
							}
							elseif ($nominaemp->vacationPremium()->exists()) 
							{
								$row[] = $nominaemp->vacationPremium->first()->netIncome;
								$row[] = 0;
								$row[] = 0;
							}
							elseif ($nominaemp->profitSharing()->exists()) 
							{
								$row[] = $nominaemp->profitSharing->first()->netIncome;
								$row[] = 0;
								$row[] = 0;
							}
							else
							{
								$row[] = 0;
								$row[] = 0;
								$row[] = 0;
							}
						}
						else
						{
							$row[] = 0;
							$row[] = 0;
							$row[] = 0;
						}
						$row[] = $nomina->nominasEmployeeNF->first()->extra_time;
						$row[] = $nomina->nominasEmployeeNF->first()->holiday;
						$row[] = $nomina->nominasEmployeeNF->first()->sundays;
						$row[] = $nomina->nominasEmployeeNF->first()->amount;
						$row[] = $nomina->nominasEmployeeNF->first()->netIncome;
						$sheet->appendRow($row);
					}
				});
			})->export('xlsx');
		}
		else
		{
			return redirect('/');
		}
	}

	public function exportSalary($id)
	{
		$selectRaw 		= '
							nomina_employees.idrealEmployee as idrealEmployee,
							CONCAT_WS(" ", real_employees.last_name, real_employees.scnd_last_name, real_employees.name) as name,
							real_employees.curp as curp,
							enterprises.name as enterpriseName,
							worker_datas.employer_register as employer_register,
							worker_datas.infonavitDiscount as infonavitDiscount,
							worker_datas.netIncome as worker_data_netIncome,
							salaries.sd as sd,
							salaries.sdi as sdi,
							salaries.workedDays as workedDays,
							nomina_employees.absence as absence,
							salaries.extra_hours as extra_hours,
							salaries.holidays as holidays,
							salaries.sundays as sundays,
							nominas_periodicity.description as periodicity,
							CONCAT_WS(" al ",nomina_employees.from_date, nomina_employees.to_date) as range_date,
							salaries.daysForImss as daysForImss,
							payment_methods.method as payment_method,
							employee_accounts.alias as alias,
							cat_banks.description as bank,
							employee_accounts.clabe as clabe,
							employee_accounts.account as account,
							employee_accounts.cardNumber as cardNumber,
							employee_accounts.branch as branch,
							salaries.salary as salary,
							salaries.loan_perception as loan_perception,
							salaries.puntuality as puntuality,
							salaries.assistance as assistance,
							(salaries.extra_time - salaries.extra_time_taxed) as extra_time,
							salaries.extra_time_taxed as extra_time_taxed,
							(salaries.holiday - salaries.holiday_taxed) as holiday,
							salaries.holiday_taxed as holiday_taxed,
							salaries.exempt_sunday as exempt_sunday,
							salaries.taxed_sunday as taxed_sunday,
							salaries.subsidy as subsidy,
							salaries.subsidyCaused as subsidyCaused,
							salaries.totalPerceptions as totalPerceptions,
							salaries.imss as imss,
							salaries.infonavit as infonavit,
							salaries.fonacot as fonacot,
							salaries.loan_retention as loan_retention,
							salaries.isrRetentions as isrRetentions,
							salaries.alimony as alimony,
							salaries.other_retention_concept as other_retention_concept,
							salaries.other_retention_amount as other_retention_amount,
							salaries.totalRetentions as totalRetentions,
							salaries.netIncome as netIncome,
							salaries.sdi as salario_base,
							salaries.daysForImss as dias_del_periodo_mensual,
							salaries.daysForImss as dias_del_periodo_bimestral,
							salaries.uma as uma,
							salaries.risk_number as prima_de_riesgo,
							IFNULL(ROUND((salaries.uma * 0.204) * salaries.daysForImss,2),0) as cuota_fija,
							IFNULL(IF((salaries.uma * 3) > salaries.sdi, 0, ROUND(((salaries.sdi - (salaries.uma * 3)) * 0.011) * salaries.daysForImss,2)),0) as excedente,
							IFNULL(ROUND((salaries.sdi * 0.007) * salaries.daysForImss,2),0) as prestaciones_en_dinero,
							IFNULL(ROUND((salaries.sdi * 0.0105) * salaries.daysForImss,2),0) as gastos_medicos_pensionados,
							IFNULL(ROUND((salaries.sdi * (salaries.risk_number / 100)) * salaries.daysForImss,2),0) as riesgo_de_trabajo,
							IFNULL(ROUND((salaries.sdi * 0.0175) * salaries.daysForImss,2),0) as invalidez_y_vida_patronal,
							IFNULL(ROUND((salaries.sdi * 0.01) * salaries.daysForImss,2),0) as guardirias_y_prestaciones,
							IFNULL(ROUND((salaries.sdi * 0.02) * salaries.daysForImss,2),0) as seguro_y_retiro,
							IFNULL(ROUND((salaries.sdi * 0.0315) * salaries.daysForImss,2),0) as cesantia_y_vejez,
							IFNULL(ROUND((salaries.sdi * 0.05) * salaries.daysForImss,2),0) as infonavit_patronal,
							IFNULL(ROUND(ROUND((salaries.uma * 0.204) * salaries.daysForImss,2) + IF((salaries.uma * 3) > salaries.sdi, 0, ROUND(((salaries.sdi - (salaries.uma * 3)) * 0.011) * salaries.daysForImss,2)) + ROUND((salaries.sdi * 0.007) * salaries.daysForImss,2) + ROUND((salaries.sdi * 0.0105) * salaries.daysForImss,2) + ROUND((salaries.sdi * (salaries.risk_number / 100)) * salaries.daysForImss,2) + ROUND((salaries.sdi * 0.0175) * salaries.daysForImss,2) + ROUND((salaries.sdi * 0.01) * salaries.daysForImss,2),2),0) as imss_mensual,
							IFNULL(ROUND(ROUND((salaries.sdi * 0.02) * salaries.daysForImss,2) + ROUND((salaries.sdi * 0.0315) * salaries.daysForImss,2),2),0) as rcv_bimestral,
							IFNULL(ROUND((salaries.sdi * 0.05) * salaries.daysForImss,2),0) as infonavit_bimestral,
							IF(salaries.alimony > 0, nomina_employees.idrealEmployee,"") as alimony_idrealEmployee,
							IF(salaries.alimony > 0, CONCAT_WS(" ", real_employees.last_name, real_employees.scnd_last_name, real_employees.name),"") as alimony_name,
							IF(salaries.alimony > 0, alimony_accounts.beneficiary,"") as alimony_beneficiary,
							IF(salaries.alimony > 0, alimony_accounts.alias,"") as alimony_alias,
							IF(salaries.alimony > 0, alimony_cat_banks.description,"") as alimony_bank,
							IF(salaries.alimony > 0, alimony_accounts.clabe,"") as alimony_clabe,
							IF(salaries.alimony > 0, alimony_accounts.account,"") as alimony_account,
							IF(salaries.alimony > 0, alimony_accounts.cardNumber,"") as alimony_cardNumber,
							IF(salaries.alimony > 0, alimony_accounts.branch,"") as alimony_branch,
							IF(salaries.alimony > 0, salaries.alimony,"") as alimony_amount

						';
		$nominaEmployees 	= DB::table('request_models')
							->selectRaw($selectRaw)
							->leftJoin('nominas','nominas.idFolio','request_models.folio')
							->leftJoin('cat_periodicities as nominas_periodicity','nominas_periodicity.c_periodicity','nominas.idCatPeriodicity')
							->leftJoin('nomina_employees','nomina_employees.idnomina','nominas.idnomina')
							->leftJoin('real_employees','real_employees.id','nomina_employees.idrealEmployee')
							->leftJoin('worker_datas','worker_datas.id','nomina_employees.idWorkingData')
							->leftJoin('enterprises','enterprises.id','worker_datas.enterprise')
							->leftJoin('salaries','salaries.idnominaEmployee','nomina_employees.idnominaEmployee')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','salaries.idpaymentMethod')
							->leftJoin('nomina_employee_accounts','nomina_employee_accounts.idSalary','salaries.idSalary')
							->leftJoin('employee_accounts','employee_accounts.id','nomina_employee_accounts.idEmployeeAccounts')
							->leftJoin('employee_accounts as alimony_accounts','alimony_accounts.id','salaries.idAccountBeneficiary')
							->leftJoin('cat_banks','cat_banks.c_bank','employee_accounts.idCatBank')
							->leftJoin('cat_banks as alimony_cat_banks','alimony_cat_banks.c_bank','alimony_accounts.idCatBank')
							->where('request_models.folio',$id)
							->where('nomina_employees.visible',1)
							->orderBy('real_employees.last_name','ASC')
							->orderBy('real_employees.scnd_last_name','ASC')
							->orderBy('real_employees.name','ASC')
							->get();

		$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
		$currencyFormat	= (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
		$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
		$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('315864')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('7C9248')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('8B3C38')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol4	= (new StyleBuilder())->setBackgroundColor('618BCF')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol5	= (new StyleBuilder())->setBackgroundColor('5C4A77')->setFontColor(Color::WHITE)->setFontBold()->build();
		$shStyleCol1	= (new StyleBuilder())->setBackgroundColor('9ECBDA')->setFontColor(Color::WHITE)->setFontBold()->build();
		$shStyleCol2	= (new StyleBuilder())->setBackgroundColor('C8D5A1')->setFontColor(Color::WHITE)->setFontBold()->build();
		$shStyleCol3	= (new StyleBuilder())->setBackgroundColor('D09996')->setFontColor(Color::WHITE)->setFontBold()->build();
		$shStyleCol4	= (new StyleBuilder())->setBackgroundColor('C9D8EF')->setFontColor(Color::WHITE)->setFontBold()->build();
		$shStyleCol5	= (new StyleBuilder())->setBackgroundColor('AEA1C4')->setFontColor(Color::WHITE)->setFontBold()->build();
		$writer			= WriterEntityFactory::createXLSXWriter();
		$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Nómina-sueldo.xlsx');
		$writer->getCurrentSheet()->setName('Sueldo');
		$headerArray	= ['INFORMACIÓN','','','','','','','','','','','','','','','','','','','','','','','','PERCEPCIONES','','','','','','','','','','','','','RETENCIONES','','','','','','','','','SUELDO NETO','CUOTAS PATRONALES','','','','','','','','','','','','','','','','',''];
		$tempHeaders		= [];
		foreach($headerArray as $k => $header)
		{
			if($k <= 23)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
			}
			elseif($k <= 36)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
			}
			elseif($k <= 45)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
			}
			elseif($k <= 46)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
			}
			else
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol5);
			}
		}
		$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
		$writer->addRow($rowFromValues);
		$subHeaderArray	= ['id','empleado','curp','empresa','registro_patronal','monto_infonavit','sueldo_neto','sd','sdi','dias_trabajados','faltas','horas_extra','dias_festivos','domingos_trabajados','periodicidad','rango_de_fechas','dias_para_imss','forma_pago','alias','banco','clabe','cuenta','tarjeta','sucursal','sueldo','prestamo_percepcion','puntualidad','asistencia','tiempo_extra_exento','tiempo_extra_gravado','dias_festivos_exento','dia_festivo_gravado','prima_dominical_exenta','prima_dominical_gravada','subsidio','subsidio_causado','total_de_percepciones','imss','infonavit','fonacot','prestamo_retencion','retencion_de_isr','pension_alimenticia','otra_retencion_concepto','otra_retencion_importe','total_de_retenciones','sueldo_neto_fiscal','salario_base','dias_del_periodo_mensual','dias_del_periodo_bimestral','uma','prima_de_riesgo','cuota_fija','excedente','prestaciones_en_dinero','gastos_medicos_pensionados','riesgo_de_trabajo','invalidez_y_vida_patronal','guardirias_y_prestaciones','seguro_y_retiro','cesantia_y_vejez','infonavit_patronal','imss_mensual','rcv_bimestral','infonavit_bimestral'];
		
		$tempHeaders	= [];
		foreach($subHeaderArray as $k => $subHeader)
		{
			if($k <= 23)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$shStyleCol1);
			}
			elseif($k <= 36)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$shStyleCol2);
			}
			elseif($k <= 45)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$shStyleCol3);
			}
			elseif($k <= 46)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$shStyleCol4);
			}
			else
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$shStyleCol5);
			}
		}
		$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
		$writer->addRow($rowFromValues);
		$tempIdrealEmployee	= '';
		$kindRow			= true;
		$flagAlimony 		= false;
		foreach($nominaEmployees as $nomina_employee)
		{
			if($tempIdrealEmployee != $nomina_employee->idrealEmployee)
			{
				$tempIdrealEmployee = $nomina_employee->idrealEmployee;
				$kindRow = !$kindRow;
				
			}
			$tmpArr = [];
			foreach($nomina_employee as $k => $n)
			{
				if (!in_array($k,['alimony_idrealEmployee','alimony_name','alimony_beneficiary','alimony_alias','alimony_bank','alimony_clabe','alimony_account','alimony_cardNumber','alimony_branch','alimony_amount'])) 
				{
					$tmpArr[] = WriterEntityFactory::createCell($n);
				}
				else
				{
					if ($k == 'alimony_amount') 
					{
						if ($n != "" && $n > 0) 
						{
							$flagAlimony = true;
						}
					}
				}
			}
			if($kindRow)
			{
				$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
			}
			else
			{
				$rowFromValues = WriterEntityFactory::createRow($tmpArr);
			}
			$writer->addRow($rowFromValues);
		}

		if ($flagAlimony) 
		{
			$writer->addNewSheetAndMakeItCurrent();
			$writer->getCurrentSheet()->setName('Pensión alimenticia');

			$headerArray	= ['INFORMACIÓN DE PENSION ALIMENTICIA','','','','','','','','',''];
			$tempHeaders		= [];
			foreach($headerArray as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			$subHeaderArray	= ['id','empleado','beneficiario','alias','banco','clabe','cuenta','tarjeta','sucursal','total',];
			
			$tempHeaders	= [];
			foreach($subHeaderArray as $k => $subHeader)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$shStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$tempIdrealEmployee	= '';
			$kindRow			= true;
			$flagAlimony 		= false;
			foreach($nominaEmployees as $nomina_employee)
			{
				if($tempIdrealEmployee != $nomina_employee->alimony_idrealEmployee)
				{
					$tempIdrealEmployee = $nomina_employee->alimony_idrealEmployee;
					$kindRow = !$kindRow;
				}
				$tmpArr = [];
				foreach($nomina_employee as $k => $n)
				{
					if (in_array($k,['alimony_idrealEmployee','alimony_name','alimony_beneficiary','alimony_alias','alimony_bank','alimony_clabe','alimony_account','alimony_cardNumber','alimony_branch','alimony_amount'])) 
					{
						$tmpArr[] = WriterEntityFactory::createCell($n);
					}
				}
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr);
				}
				$writer->addRow($rowFromValues);
			}
		}
		return $writer->close();
	}

	public function exportBonus($id)
	{
		$selectRaw 		= '
							nomina_employees.idrealEmployee as idrealEmployee,
							CONCAT_WS(" ", real_employees.last_name, real_employees.scnd_last_name, real_employees.name) as name,
							bonuses.sd as sd,
							bonuses.sdi as sdi,
							bonuses.dateOfAdmission as dateOfAdmission,
							bonuses.daysForBonuses as daysForBonuses,
							bonuses.proportionalPartForChristmasBonus as proportionalPartForChristmasBonus,
							payment_methods.method as payment_method,
							employee_accounts.alias as alias,
							cat_banks.description as bank,
							employee_accounts.clabe as clabe,
							employee_accounts.account as account,
							employee_accounts.cardNumber as cardNumber,
							employee_accounts.branch as branch,
							bonuses.exemptBonus as exemptBonus,
							bonuses.taxableBonus as taxableBonus,
							bonuses.totalPerceptions as totalPerceptions,
							bonuses.isr as isr,
							bonuses.alimony as alimony,
							bonuses.totalTaxes as totalTaxes,
							bonuses.netIncome as netIncome,
							IF(bonuses.alimony > 0, nomina_employees.idrealEmployee,"") as alimony_idrealEmployee,
							IF(bonuses.alimony > 0, CONCAT_WS(" ", real_employees.last_name, real_employees.scnd_last_name, real_employees.name),"") as alimony_name,
							IF(bonuses.alimony > 0, alimony_accounts.beneficiary,"") as alimony_beneficiary,
							IF(bonuses.alimony > 0, alimony_accounts.alias,"") as alimony_alias,
							IF(bonuses.alimony > 0, alimony_cat_banks.description,"") as alimony_bank,
							IF(bonuses.alimony > 0, alimony_accounts.clabe,"") as alimony_clabe,
							IF(bonuses.alimony > 0, alimony_accounts.account,"") as alimony_account,
							IF(bonuses.alimony > 0, alimony_accounts.cardNumber,"") as alimony_cardNumber,
							IF(bonuses.alimony > 0, alimony_accounts.branch,"") as alimony_branch,
							IF(bonuses.alimony > 0, bonuses.alimony,"") as alimony_amount

						';
		$nominaEmployees 	= DB::table('request_models')
							->selectRaw($selectRaw)
							->leftJoin('nominas','nominas.idFolio','request_models.folio')
							->leftJoin('cat_periodicities as nominas_periodicity','nominas_periodicity.c_periodicity','nominas.idCatPeriodicity')
							->leftJoin('nomina_employees','nomina_employees.idnomina','nominas.idnomina')
							->leftJoin('real_employees','real_employees.id','nomina_employees.idrealEmployee')
							->leftJoin('bonuses','bonuses.idnominaEmployee','nomina_employees.idnominaEmployee')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','bonuses.idpaymentMethod')
							->leftJoin('nomina_employee_accounts','nomina_employee_accounts.idBonus','bonuses.idBonus')
							->leftJoin('employee_accounts','employee_accounts.id','nomina_employee_accounts.idEmployeeAccounts')
							->leftJoin('employee_accounts as alimony_accounts','alimony_accounts.id','bonuses.idAccountBeneficiary')
							->leftJoin('cat_banks','cat_banks.c_bank','employee_accounts.idCatBank')
							->leftJoin('cat_banks as alimony_cat_banks','alimony_cat_banks.c_bank','alimony_accounts.idCatBank')
							->where('request_models.folio',$id)
							->where('nomina_employees.visible',1)
							->orderBy('real_employees.last_name','ASC')
							->orderBy('real_employees.scnd_last_name','ASC')
							->orderBy('real_employees.name','ASC')
							->get();

		$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
		$currencyFormat	= (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
		$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
		$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('f8cd5c')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('7fc544')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('EE881F')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol4	= (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol5	= (new StyleBuilder())->setBackgroundColor('5C4A77')->setFontColor(Color::WHITE)->setFontBold()->build();
		$writer			= WriterEntityFactory::createXLSXWriter();
		$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Nómina-aguinaldo.xlsx');
		$writer->getCurrentSheet()->setName('Aguinaldo');
		$headerArray	= ['INFORMACIÓN','','','','','','','','','','','','','','PERCEPCIONES','','','RETENCIONES','','',''];
		$tempHeaders		= [];
		foreach($headerArray as $k => $header)
		{
			if($k <= 13)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
			}
			elseif($k <= 16)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
			}
			elseif($k <= 19)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
			}
			else
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
			}
		}
		$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
		$writer->addRow($rowFromValues);
		$subHeaderArray	= ['id','empleado','sd','sdi','fecha_de_ingreso','dias_para_aguinaldo','parte_proporcional_para_aguinaldo','forma_pago','alias','banco','clabe','cuenta','tarjeta','sucursal','aguinaldo_exento','aguinaldo_gravable','total_de_percepciones','isr','pension_alimenticia','total_de_retenciones','sueldo_neto'];
		
		$tempHeaders	= [];
		foreach($subHeaderArray as $k => $subHeader)
		{
			if($k <= 13)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol1);
			}
			elseif($k <= 16)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
			}
			elseif($k <= 19)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
			}
			else
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol4);
			}
		}
		$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
		$writer->addRow($rowFromValues);
		$tempIdrealEmployee	= '';
		$kindRow			= true;
		$flagAlimony 		= false;
		foreach($nominaEmployees as $nomina_employee)
		{
			if($tempIdrealEmployee != $nomina_employee->idrealEmployee)
			{
				$tempIdrealEmployee = $nomina_employee->idrealEmployee;
				$kindRow = !$kindRow;
				
			}
			$tmpArr = [];
			foreach($nomina_employee as $k => $n)
			{
				if (!in_array($k,['alimony_idrealEmployee','alimony_name','alimony_beneficiary','alimony_alias','alimony_bank','alimony_clabe','alimony_account','alimony_cardNumber','alimony_branch','alimony_amount'])) 
				{
					$tmpArr[] = WriterEntityFactory::createCell($n);
				}
				else
				{
					if ($k == 'alimony_amount') 
					{
						if ($n != "" && $n > 0) 
						{
							$flagAlimony = true;
						}
					}
				}
			}
			if($kindRow)
			{
				$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
			}
			else
			{
				$rowFromValues = WriterEntityFactory::createRow($tmpArr);
			}
			$writer->addRow($rowFromValues);
		}

		if ($flagAlimony) 
		{
			$writer->addNewSheetAndMakeItCurrent();
			$writer->getCurrentSheet()->setName('Pensión alimenticia');

			$headerArray	= ['INFORMACIÓN DE PENSION ALIMENTICIA','','','','','','','','',''];
			$tempHeaders		= [];
			foreach($headerArray as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			$subHeaderArray	= ['id','empleado','beneficiario','alias','banco','clabe','cuenta','tarjeta','sucursal','total',];
			
			$tempHeaders	= [];
			foreach($subHeaderArray as $k => $subHeader)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$shStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$tempIdrealEmployee	= '';
			$kindRow			= true;
			$flagAlimony 		= false;
			foreach($nominaEmployees as $nomina_employee)
			{
				if($tempIdrealEmployee != $nomina_employee->alimony_idrealEmployee)
				{
					$tempIdrealEmployee = $nomina_employee->alimony_idrealEmployee;
					$kindRow = !$kindRow;
				}
				$tmpArr = [];
				foreach($nomina_employee as $k => $n)
				{
					if (in_array($k,['alimony_idrealEmployee','alimony_name','alimony_beneficiary','alimony_alias','alimony_bank','alimony_clabe','alimony_account','alimony_cardNumber','alimony_branch','alimony_amount'])) 
					{
						$tmpArr[] = WriterEntityFactory::createCell($n);
					}
				}
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr);
				}
				$writer->addRow($rowFromValues);
			}
		}
		return $writer->close();
	}

	public function exportSettlement($id)
	{
		$selectRaw 		= '
							nomina_employees.idrealEmployee as idrealEmployee,
							CONCAT_WS(" ", real_employees.last_name, real_employees.scnd_last_name, real_employees.name) as name,
							liquidations.sd as sd,
							liquidations.sdi as sdi,
							IF(liquidations.admissionDate IS NOT NULL, liquidations.admissionDate, worker_datas.admissionDate) as admissionDate,
							liquidations.downDate as downDate,
							liquidations.fullYears as fullYears,
							liquidations.workedDays as workedDays,
							liquidations.holidayDays as holidayDays,
							liquidations.bonusDays as bonusDays,
							payment_methods.method as payment_method,
							employee_accounts.alias as alias,
							cat_banks.description as bank,
							employee_accounts.clabe as clabe,
							employee_accounts.account as account,
							employee_accounts.cardNumber as cardNumber,
							employee_accounts.branch as branch,
							liquidations.seniorityPremium as seniorityPremium,
							liquidations.exemptCompensation as exemptCompensation,
							liquidations.taxedCompensation as taxedCompensation,
							liquidations.holidays as holidays,
							liquidations.exemptBonus as exemptBonus,
							liquidations.taxableBonus as taxableBonus,
							liquidations.holidayPremiumExempt as holidayPremiumExempt,
							liquidations.holidayPremiumTaxed as holidayPremiumTaxed,
							liquidations.otherPerception as otherPerception,
							liquidations.totalPerceptions as totalPerceptions,
							liquidations.isr as isr,
							liquidations.alimony as alimony,
							liquidations.other_retention as other_retention,
							liquidations.totalRetentions as totalRetentions,
							liquidations.netIncome as netIncome,
							IF(liquidations.alimony > 0, nomina_employees.idrealEmployee,"") as alimony_idrealEmployee,
							IF(liquidations.alimony > 0, CONCAT_WS(" ", real_employees.last_name, real_employees.scnd_last_name, real_employees.name),"") as alimony_name,
							IF(liquidations.alimony > 0, alimony_accounts.beneficiary,"") as alimony_beneficiary,
							IF(liquidations.alimony > 0, alimony_accounts.alias,"") as alimony_alias,
							IF(liquidations.alimony > 0, alimony_cat_banks.description,"") as alimony_bank,
							IF(liquidations.alimony > 0, alimony_accounts.clabe,"") as alimony_clabe,
							IF(liquidations.alimony > 0, alimony_accounts.account,"") as alimony_account,
							IF(liquidations.alimony > 0, alimony_accounts.cardNumber,"") as alimony_cardNumber,
							IF(liquidations.alimony > 0, alimony_accounts.branch,"") as alimony_branch,
							IF(liquidations.alimony > 0, liquidations.alimony,"") as alimony_amount

						';
		$nominaEmployees 	= DB::table('request_models')
							->selectRaw($selectRaw)
							->leftJoin('nominas','nominas.idFolio','request_models.folio')
							->leftJoin('cat_periodicities as nominas_periodicity','nominas_periodicity.c_periodicity','nominas.idCatPeriodicity')
							->leftJoin('nomina_employees','nomina_employees.idnomina','nominas.idnomina')
							->leftJoin('real_employees','real_employees.id','nomina_employees.idrealEmployee')
							->leftJoin('worker_datas','worker_datas.id','nomina_employees.idworkingData')
							->leftJoin('liquidations','liquidations.idnominaEmployee','nomina_employees.idnominaEmployee')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','liquidations.idpaymentMethod')
							->leftJoin('nomina_employee_accounts','nomina_employee_accounts.idLiquidation','liquidations.idLiquidation')
							->leftJoin('employee_accounts','employee_accounts.id','nomina_employee_accounts.idEmployeeAccounts')
							->leftJoin('employee_accounts as alimony_accounts','alimony_accounts.id','liquidations.idAccountBeneficiary')
							->leftJoin('cat_banks','cat_banks.c_bank','employee_accounts.idCatBank')
							->leftJoin('cat_banks as alimony_cat_banks','alimony_cat_banks.c_bank','alimony_accounts.idCatBank')
							->where('request_models.folio',$id)
							->where('nomina_employees.visible',1)
							->orderBy('real_employees.last_name','ASC')
							->orderBy('real_employees.scnd_last_name','ASC')
							->orderBy('real_employees.name','ASC')
							->get();

		$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
		$currencyFormat	= (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
		$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
		$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('f8cd5c')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('7fc544')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('EE881F')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol4	= (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol5	= (new StyleBuilder())->setBackgroundColor('5C4A77')->setFontColor(Color::WHITE)->setFontBold()->build();
		$writer			= WriterEntityFactory::createXLSXWriter();
		$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Nómina-finiquito.xlsx');
		$writer->getCurrentSheet()->setName('Finiquito');
		$headerArray	= ['INFORMACIÓN','','','','','','','','','','','','','','','','','PERCEPCIONES','','','','','','','','','','RETENCIONES','','','',''];
		$tempHeaders		= [];
		foreach($headerArray as $k => $header)
		{
			if($k <= 9)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
			}
			elseif($k <= 16)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
			}
			elseif($k <= 19)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
			}
			else
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
			}
		}
		$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
		$writer->addRow($rowFromValues);
		$subHeaderArray	= ['id', 'empleado', 'sd', 'sdi', 'fecha_de_ingreso', 'fecha_de_baja', 'anios_completos', 'dias_trabajados', 'dias_para_vacaciones', 'dias_para_aguinaldo', 'forma_pago', 'alias', 'banco', 'clabe', 'cuenta', 'tarjeta', 'sucursal', 'prima_de_antiguedad', 'indemnizacion_exenta', 'indemnizacion_gravada', 'vacaciones', 'aguinaldo_exento', 'aguinaldo_gravable', 'prima_vacacional_exenta', 'prima_vacacional_gravada', 'otras_percepciones', 'total_de_percepciones', 'isr', 'pension_alimenticia','otras_retenciones','total_de_retenciones', 'sueldo_neto'];
		
		$tempHeaders	= [];
		foreach($subHeaderArray as $k => $subHeader)
		{
			if($k <= 9)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol1);
			}
			elseif($k <= 16)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
			}
			elseif($k <= 19)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
			}
			else
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol4);
			}
		}
		$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
		$writer->addRow($rowFromValues);
		$tempIdrealEmployee	= '';
		$kindRow			= true;
		$flagAlimony 		= false;
		foreach($nominaEmployees as $nomina_employee)
		{
			if($tempIdrealEmployee != $nomina_employee->idrealEmployee)
			{
				$tempIdrealEmployee = $nomina_employee->idrealEmployee;
				$kindRow = !$kindRow;
				
			}
			$tmpArr = [];
			foreach($nomina_employee as $k => $n)
			{
				if (!in_array($k,['alimony_idrealEmployee','alimony_name','alimony_beneficiary','alimony_alias','alimony_bank','alimony_clabe','alimony_account','alimony_cardNumber','alimony_branch','alimony_amount'])) 
				{
					$tmpArr[] = WriterEntityFactory::createCell($n);
				}
				else
				{
					if ($k == 'alimony_amount') 
					{
						if ($n != "" && $n > 0) 
						{
							$flagAlimony = true;
						}
					}
				}
			}
			if($kindRow)
			{
				$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
			}
			else
			{
				$rowFromValues = WriterEntityFactory::createRow($tmpArr);
			}
			$writer->addRow($rowFromValues);
		}

		if ($flagAlimony) 
		{
			$writer->addNewSheetAndMakeItCurrent();
			$writer->getCurrentSheet()->setName('Pensión alimenticia');

			$headerArray	= ['INFORMACIÓN DE PENSION ALIMENTICIA','','','','','','','','',''];
			$tempHeaders		= [];
			foreach($headerArray as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			$subHeaderArray	= ['id','empleado','beneficiario','alias','banco','clabe','cuenta','tarjeta','sucursal','total',];
			
			$tempHeaders	= [];
			foreach($subHeaderArray as $k => $subHeader)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$shStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$tempIdrealEmployee	= '';
			$kindRow			= true;
			$flagAlimony 		= false;
			foreach($nominaEmployees as $nomina_employee)
			{
				if($tempIdrealEmployee != $nomina_employee->alimony_idrealEmployee)
				{
					$tempIdrealEmployee = $nomina_employee->alimony_idrealEmployee;
					$kindRow = !$kindRow;
				}
				$tmpArr = [];
				foreach($nomina_employee as $k => $n)
				{
					if (in_array($k,['alimony_idrealEmployee','alimony_name','alimony_beneficiary','alimony_alias','alimony_bank','alimony_clabe','alimony_account','alimony_cardNumber','alimony_branch','alimony_amount'])) 
					{
						$tmpArr[] = WriterEntityFactory::createCell($n);
					}
				}
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr);
				}
				$writer->addRow($rowFromValues);
			}
		}
		return $writer->close();
	}

	public function exportLiquidation($id)
	{
		$selectRaw 		= '
							nomina_employees.idrealEmployee as idrealEmployee,
							CONCAT_WS(" ", real_employees.last_name, real_employees.scnd_last_name, real_employees.name) as name,
							liquidations.sd as sd,
							liquidations.sdi as sdi,
							IF(liquidations.admissionDate IS NOT NULL, liquidations.admissionDate, worker_datas.admissionDate) as admissionDate,
							liquidations.downDate as downDate,
							liquidations.fullYears as fullYears,
							liquidations.workedDays as workedDays,
							liquidations.holidayDays as holidayDays,
							liquidations.bonusDays as bonusDays,
							payment_methods.method as payment_method,
							employee_accounts.alias as alias,
							cat_banks.description as bank,
							employee_accounts.clabe as clabe,
							employee_accounts.account as account,
							employee_accounts.cardNumber as cardNumber,
							employee_accounts.branch as branch,
							liquidations.liquidationSalary as liquidationSalary,
							liquidations.twentyDaysPerYearOfServices as twentyDaysPerYearOfServices,
							liquidations.seniorityPremium as seniorityPremium,
							liquidations.exemptCompensation as exemptCompensation,
							liquidations.taxedCompensation as taxedCompensation,
							liquidations.holidays as holidays,
							liquidations.exemptBonus as exemptBonus,
							liquidations.taxableBonus as taxableBonus,
							liquidations.holidayPremiumExempt as holidayPremiumExempt,
							liquidations.holidayPremiumTaxed as holidayPremiumTaxed,
							liquidations.otherPerception as otherPerception,
							liquidations.totalPerceptions as totalPerceptions,
							liquidations.isr as isr,
							liquidations.alimony as alimony,
							liquidations.other_retention as other_retention,
							liquidations.totalRetentions as totalRetentions,
							liquidations.netIncome as netIncome,
							IF(liquidations.alimony > 0, nomina_employees.idrealEmployee,"") as alimony_idrealEmployee,
							IF(liquidations.alimony > 0, CONCAT_WS(" ", real_employees.last_name, real_employees.scnd_last_name, real_employees.name),"") as alimony_name,
							IF(liquidations.alimony > 0, alimony_accounts.beneficiary,"") as alimony_beneficiary,
							IF(liquidations.alimony > 0, alimony_accounts.alias,"") as alimony_alias,
							IF(liquidations.alimony > 0, alimony_cat_banks.description,"") as alimony_bank,
							IF(liquidations.alimony > 0, alimony_accounts.clabe,"") as alimony_clabe,
							IF(liquidations.alimony > 0, alimony_accounts.account,"") as alimony_account,
							IF(liquidations.alimony > 0, alimony_accounts.cardNumber,"") as alimony_cardNumber,
							IF(liquidations.alimony > 0, alimony_accounts.branch,"") as alimony_branch,
							IF(liquidations.alimony > 0, liquidations.alimony,"") as alimony_amount

						';
		$nominaEmployees 	= DB::table('request_models')
							->selectRaw($selectRaw)
							->leftJoin('nominas','nominas.idFolio','request_models.folio')
							->leftJoin('cat_periodicities as nominas_periodicity','nominas_periodicity.c_periodicity','nominas.idCatPeriodicity')
							->leftJoin('nomina_employees','nomina_employees.idnomina','nominas.idnomina')
							->leftJoin('real_employees','real_employees.id','nomina_employees.idrealEmployee')
							->leftJoin('worker_datas','worker_datas.id','nomina_employees.idworkingData')
							->leftJoin('liquidations','liquidations.idnominaEmployee','nomina_employees.idnominaEmployee')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','liquidations.idpaymentMethod')
							->leftJoin('nomina_employee_accounts','nomina_employee_accounts.idLiquidation','liquidations.idLiquidation')
							->leftJoin('employee_accounts','employee_accounts.id','nomina_employee_accounts.idEmployeeAccounts')
							->leftJoin('employee_accounts as alimony_accounts','alimony_accounts.id','liquidations.idAccountBeneficiary')
							->leftJoin('cat_banks','cat_banks.c_bank','employee_accounts.idCatBank')
							->leftJoin('cat_banks as alimony_cat_banks','alimony_cat_banks.c_bank','alimony_accounts.idCatBank')
							->where('request_models.folio',$id)
							->where('nomina_employees.visible',1)
							->orderBy('real_employees.last_name','ASC')
							->orderBy('real_employees.scnd_last_name','ASC')
							->orderBy('real_employees.name','ASC')
							->get();

		$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
		$currencyFormat	= (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
		$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
		$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('f8cd5c')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('7fc544')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('EE881F')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol4	= (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol5	= (new StyleBuilder())->setBackgroundColor('5C4A77')->setFontColor(Color::WHITE)->setFontBold()->build();
		$writer			= WriterEntityFactory::createXLSXWriter();
		$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Nómina-liquidación.xlsx');
		$writer->getCurrentSheet()->setName('Liquidación');
		$headerArray	= ['INFORMACIÓN','','','','','','','','','','','','','','','','','PERCEPCIONES','','','','','','','','','','','','RETENCIONES','','',''];
		$tempHeaders		= [];
		foreach($headerArray as $k => $header)
		{
			if($k <= 16)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
			}
			elseif($k <= 28)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
			}
			elseif($k <= 32)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
			}
			else
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
			}
		}
		$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
		$writer->addRow($rowFromValues);

		$subHeaderArray	= ['id','empleado','sd','sdi','fecha_de_ingreso','fecha_de_baja','anios_completos','dias_trabajados','dias_para_vacaciones','dias_para_aguinaldo','forma_pago','alias','banco','clabe','cuenta','tarjeta','sucursal','sueldo_por_liquidacion','20_dias_por_anio_de_servicio','prima_de_antiguedad','indemnizacion_exenta','indemnizacion_gravada','vacaciones','aguinaldo_exento','aguinaldo_gravable','prima_vacacional_exenta','prima_vacacional_gravada','otras_percepciones','total_de_percepciones','isr','pension_alimenticia','otras_retenciones','total_de_retenciones','sueldo_neto'];
		
		$tempHeaders	= [];
		foreach($subHeaderArray as $k => $subHeader)
		{
			if($k <= 16)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol1);
			}
			elseif($k <= 28)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
			}
			elseif($k <= 32)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
			}
			else
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol4);
			}
		}
		$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
		$writer->addRow($rowFromValues);
		$tempIdrealEmployee	= '';
		$kindRow			= true;
		$flagAlimony 		= false;
		foreach($nominaEmployees as $nomina_employee)
		{
			if($tempIdrealEmployee != $nomina_employee->idrealEmployee)
			{
				$tempIdrealEmployee = $nomina_employee->idrealEmployee;
				$kindRow = !$kindRow;
				
			}
			$tmpArr = [];
			foreach($nomina_employee as $k => $n)
			{
				if (!in_array($k,['alimony_idrealEmployee','alimony_name','alimony_beneficiary','alimony_alias','alimony_bank','alimony_clabe','alimony_account','alimony_cardNumber','alimony_branch','alimony_amount'])) 
				{
					$tmpArr[] = WriterEntityFactory::createCell($n);
				}
				else
				{
					if ($k == 'alimony_amount') 
					{
						if ($n != "" && $n > 0) 
						{
							$flagAlimony = true;
						}
					}
				}
			}
			if($kindRow)
			{
				$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
			}
			else
			{
				$rowFromValues = WriterEntityFactory::createRow($tmpArr);
			}
			$writer->addRow($rowFromValues);
		}

		if ($flagAlimony) 
		{
			$writer->addNewSheetAndMakeItCurrent();
			$writer->getCurrentSheet()->setName('Pensión alimenticia');

			$headerArray	= ['INFORMACIÓN DE PENSION ALIMENTICIA','','','','','','','','',''];
			$tempHeaders		= [];
			foreach($headerArray as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			$subHeaderArray	= ['id','empleado','beneficiario','alias','banco','clabe','cuenta','tarjeta','sucursal','total',];
			
			$tempHeaders	= [];
			foreach($subHeaderArray as $k => $subHeader)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$shStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$tempIdrealEmployee	= '';
			$kindRow			= true;
			$flagAlimony 		= false;
			foreach($nominaEmployees as $nomina_employee)
			{
				if($tempIdrealEmployee != $nomina_employee->alimony_idrealEmployee)
				{
					$tempIdrealEmployee = $nomina_employee->alimony_idrealEmployee;
					$kindRow = !$kindRow;
				}
				$tmpArr = [];
				foreach($nomina_employee as $k => $n)
				{
					if (in_array($k,['alimony_idrealEmployee','alimony_name','alimony_beneficiary','alimony_alias','alimony_bank','alimony_clabe','alimony_account','alimony_cardNumber','alimony_branch','alimony_amount'])) 
					{
						$tmpArr[] = WriterEntityFactory::createCell($n);
					}
				}
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr);
				}
				$writer->addRow($rowFromValues);
			}
		}
		return $writer->close();
	}

	public function exportVacationPremium($id)
	{
		$selectRaw 		= '
							nomina_employees.idrealEmployee as idrealEmployee,
							CONCAT_WS(" ", real_employees.last_name, real_employees.scnd_last_name, real_employees.name) as name,
							vacation_premia.sd as sd,
							vacation_premia.sdi as sdi,
							vacation_premia.dateOfAdmission as dateOfAdmission,
							vacation_premia.workedDays as workedDays,
							vacation_premia.holidaysDays as holidaysDays,
							payment_methods.method as payment_method,
							employee_accounts.alias as alias,
							cat_banks.description as bank,
							employee_accounts.clabe as clabe,
							employee_accounts.account as account,
							employee_accounts.cardNumber as cardNumber,
							employee_accounts.branch as branch,
							vacation_premia.holidays as holidays,
							vacation_premia.exemptHolidayPremium as exemptHolidayPremium,
							vacation_premia.holidayPremiumTaxed as holidayPremiumTaxed,
							vacation_premia.totalPerceptions as totalPerceptions,
							vacation_premia.isr as isr,
							vacation_premia.alimony as alimony,
							vacation_premia.totalTaxes as totalTaxes,
							vacation_premia.netIncome as netIncome,
							IF(vacation_premia.alimony > 0, nomina_employees.idrealEmployee,"") as alimony_idrealEmployee,
							IF(vacation_premia.alimony > 0, CONCAT_WS(" ", real_employees.last_name, real_employees.scnd_last_name, real_employees.name),"") as alimony_name,
							IF(vacation_premia.alimony > 0, alimony_accounts.beneficiary,"") as alimony_beneficiary,
							IF(vacation_premia.alimony > 0, alimony_accounts.alias,"") as alimony_alias,
							IF(vacation_premia.alimony > 0, alimony_cat_banks.description,"") as alimony_bank,
							IF(vacation_premia.alimony > 0, alimony_accounts.clabe,"") as alimony_clabe,
							IF(vacation_premia.alimony > 0, alimony_accounts.account,"") as alimony_account,
							IF(vacation_premia.alimony > 0, alimony_accounts.cardNumber,"") as alimony_cardNumber,
							IF(vacation_premia.alimony > 0, alimony_accounts.branch,"") as alimony_branch,
							IF(vacation_premia.alimony > 0, vacation_premia.alimony,"") as alimony_amount

						';
		$nominaEmployees 	= DB::table('request_models')
							->selectRaw($selectRaw)
							->leftJoin('nominas','nominas.idFolio','request_models.folio')
							->leftJoin('cat_periodicities as nominas_periodicity','nominas_periodicity.c_periodicity','nominas.idCatPeriodicity')
							->leftJoin('nomina_employees','nomina_employees.idnomina','nominas.idnomina')
							->leftJoin('real_employees','real_employees.id','nomina_employees.idrealEmployee')
							->leftJoin('vacation_premia','vacation_premia.idnominaEmployee','nomina_employees.idnominaEmployee')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','vacation_premia.idpaymentMethod')
							->leftJoin('nomina_employee_accounts','nomina_employee_accounts.idvacationPremium','vacation_premia.idvacationPremium')
							->leftJoin('employee_accounts','employee_accounts.id','nomina_employee_accounts.idEmployeeAccounts')
							->leftJoin('employee_accounts as alimony_accounts','alimony_accounts.id','vacation_premia.idAccountBeneficiary')
							->leftJoin('cat_banks','cat_banks.c_bank','employee_accounts.idCatBank')
							->leftJoin('cat_banks as alimony_cat_banks','alimony_cat_banks.c_bank','alimony_accounts.idCatBank')
							->where('request_models.folio',$id)
							->where('nomina_employees.visible',1)
							->orderBy('real_employees.last_name','ASC')
							->orderBy('real_employees.scnd_last_name','ASC')
							->orderBy('real_employees.name','ASC')
							->get();

		$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
		$currencyFormat	= (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
		$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
		$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('f8cd5c')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('7fc544')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('EE881F')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol4	= (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol5	= (new StyleBuilder())->setBackgroundColor('5C4A77')->setFontColor(Color::WHITE)->setFontBold()->build();
		$writer			= WriterEntityFactory::createXLSXWriter();
		$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Nómina-prima-vacaciones.xlsx');
		$writer->getCurrentSheet()->setName('Prima vacacional');
		$headerArray	= ['INFORMACIÓN','','','','','','','','','','','','','','PERCEPCIONES','','','','RETENCIONES','','',''];
		$tempHeaders		= [];
		foreach($headerArray as $k => $header)
		{
			if($k <= 13)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
			}
			elseif($k <= 17)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
			}
			elseif($k <= 20)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
			}
			else
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
			}
		}
		$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
		$writer->addRow($rowFromValues);

		$subHeaderArray	= ['id','empleado','sd','sdi','fecha_de_ingreso','dias_trabajados','dias_para_vacaciones','forma_pago','alias','banco','clabe','cuenta','tarjeta','sucursal','vacaciones','prima_vacacional_exenta','prima_vacacional_gravada','total_de_percepciones','isr','pension_alimenticia','total_de_retenciones','sueldo_neto'];
		
		$tempHeaders	= [];
		foreach($subHeaderArray as $k => $subHeader)
		{
			if($k <= 13)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol1);
			}
			elseif($k <= 17)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
			}
			elseif($k <= 20)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
			}
			else
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol4);
			}
		}
		$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
		$writer->addRow($rowFromValues);
		$tempIdrealEmployee	= '';
		$kindRow			= true;
		$flagAlimony 		= false;
		foreach($nominaEmployees as $nomina_employee)
		{
			if($tempIdrealEmployee != $nomina_employee->idrealEmployee)
			{
				$tempIdrealEmployee = $nomina_employee->idrealEmployee;
				$kindRow = !$kindRow;
				
			}
			$tmpArr = [];
			foreach($nomina_employee as $k => $n)
			{
				if (!in_array($k,['alimony_idrealEmployee','alimony_name','alimony_beneficiary','alimony_alias','alimony_bank','alimony_clabe','alimony_account','alimony_cardNumber','alimony_branch','alimony_amount'])) 
				{
					$tmpArr[] = WriterEntityFactory::createCell($n);
				}
				else
				{
					if ($k == 'alimony_amount') 
					{
						if ($n != "" && $n > 0) 
						{
							$flagAlimony = true;
						}
					}
				}
			}
			if($kindRow)
			{
				$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
			}
			else
			{
				$rowFromValues = WriterEntityFactory::createRow($tmpArr);
			}
			$writer->addRow($rowFromValues);
		}

		if ($flagAlimony) 
		{
			$writer->addNewSheetAndMakeItCurrent();
			$writer->getCurrentSheet()->setName('Pensión alimenticia');

			$headerArray	= ['INFORMACIÓN DE PENSION ALIMENTICIA','','','','','','','','',''];
			$tempHeaders		= [];
			foreach($headerArray as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			$subHeaderArray	= ['id','empleado','beneficiario','alias','banco','clabe','cuenta','tarjeta','sucursal','total',];
			
			$tempHeaders	= [];
			foreach($subHeaderArray as $k => $subHeader)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$shStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$tempIdrealEmployee	= '';
			$kindRow			= true;
			$flagAlimony 		= false;
			foreach($nominaEmployees as $nomina_employee)
			{
				if($tempIdrealEmployee != $nomina_employee->alimony_idrealEmployee)
				{
					$tempIdrealEmployee = $nomina_employee->alimony_idrealEmployee;
					$kindRow = !$kindRow;
				}
				$tmpArr = [];
				foreach($nomina_employee as $k => $n)
				{
					if (in_array($k,['alimony_idrealEmployee','alimony_name','alimony_beneficiary','alimony_alias','alimony_bank','alimony_clabe','alimony_account','alimony_cardNumber','alimony_branch','alimony_amount'])) 
					{
						$tmpArr[] = WriterEntityFactory::createCell($n);
					}
				}
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr);
				}
				$writer->addRow($rowFromValues);
			}
		}
		return $writer->close();
	}

	public function exportProfitSharing($id)
	{
		$selectRaw 		= '
							nomina_employees.idrealEmployee as idrealEmployee,
							CONCAT_WS(" ", real_employees.last_name, real_employees.scnd_last_name, real_employees.name) as name,
							worker_datas.imssDate as imssDate,
							profit_sharings.sd as sd,
							profit_sharings.sdi as sdi,
							profit_sharings.workedDays as workedDays,
							profit_sharings.totalSalary as totalSalary,
							profit_sharings.ptuForDays as ptuForDays,
							profit_sharings.ptuForSalary as ptuForSalary,
							profit_sharings.totalPtu as totalPtu,
							payment_methods.method as payment_method,
							employee_accounts.alias as alias,
							cat_banks.description as bank,
							employee_accounts.clabe as clabe,
							employee_accounts.account as account,
							employee_accounts.cardNumber as cardNumber,
							employee_accounts.branch as branch,
							profit_sharings.exemptPtu as exemptPtu,
							profit_sharings.taxedPtu as taxedPtu,
							profit_sharings.totalPerceptions as totalPerceptions,
							profit_sharings.isrRetentions as isrRetentions,
							profit_sharings.alimony as alimony,
							profit_sharings.totalRetentions as totalRetentions,
							profit_sharings.netIncome as netIncome,
							IF(profit_sharings.alimony > 0, nomina_employees.idrealEmployee,"") as alimony_idrealEmployee,
							IF(profit_sharings.alimony > 0, CONCAT_WS(" ", real_employees.last_name, real_employees.scnd_last_name, real_employees.name),"") as alimony_name,
							IF(profit_sharings.alimony > 0, alimony_accounts.beneficiary,"") as alimony_beneficiary,
							IF(profit_sharings.alimony > 0, alimony_accounts.alias,"") as alimony_alias,
							IF(profit_sharings.alimony > 0, alimony_cat_banks.description,"") as alimony_bank,
							IF(profit_sharings.alimony > 0, alimony_accounts.clabe,"") as alimony_clabe,
							IF(profit_sharings.alimony > 0, alimony_accounts.account,"") as alimony_account,
							IF(profit_sharings.alimony > 0, alimony_accounts.cardNumber,"") as alimony_cardNumber,
							IF(profit_sharings.alimony > 0, alimony_accounts.branch,"") as alimony_branch,
							IF(profit_sharings.alimony > 0, profit_sharings.alimony,"") as alimony_amount

						';
		$nominaEmployees 	= DB::table('request_models')
							->selectRaw($selectRaw)
							->leftJoin('nominas','nominas.idFolio','request_models.folio')
							->leftJoin('cat_periodicities as nominas_periodicity','nominas_periodicity.c_periodicity','nominas.idCatPeriodicity')
							->leftJoin('nomina_employees','nomina_employees.idnomina','nominas.idnomina')
							->leftJoin('real_employees','real_employees.id','nomina_employees.idrealEmployee')
							->leftJoin('worker_datas','worker_datas.id','nomina_employees.idworkingData')
							->leftJoin('profit_sharings','profit_sharings.idnominaEmployee','nomina_employees.idnominaEmployee')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','profit_sharings.idpaymentMethod')
							->leftJoin('nomina_employee_accounts','nomina_employee_accounts.idprofitSharing','profit_sharings.idprofitSharing')
							->leftJoin('employee_accounts','employee_accounts.id','nomina_employee_accounts.idEmployeeAccounts')
							->leftJoin('employee_accounts as alimony_accounts','alimony_accounts.id','profit_sharings.idAccountBeneficiary')
							->leftJoin('cat_banks','cat_banks.c_bank','employee_accounts.idCatBank')
							->leftJoin('cat_banks as alimony_cat_banks','alimony_cat_banks.c_bank','alimony_accounts.idCatBank')
							->where('request_models.folio',$id)
							->where('nomina_employees.visible',1)
							->orderBy('real_employees.last_name','ASC')
							->orderBy('real_employees.scnd_last_name','ASC')
							->orderBy('real_employees.name','ASC')
							->get();

		$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
		$currencyFormat	= (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
		$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
		$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('f8cd5c')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('7fc544')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('EE881F')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol4	= (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setFontBold()->build();
		$mhStyleCol5	= (new StyleBuilder())->setBackgroundColor('5C4A77')->setFontColor(Color::WHITE)->setFontBold()->build();
		$writer			= WriterEntityFactory::createXLSXWriter();
		$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Nómina-reparto-de-utilidades.xlsx');
		$writer->getCurrentSheet()->setName('Reparto de Utilidades');
		$headerArray	= ['INFORMACIÓN','','','','','','','','','','','','','','','','','PERCEPCIONES','','','RETENCIONES','','',''];
		$tempHeaders		= [];
		foreach($headerArray as $k => $header)
		{
			if($k <= 16)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
			}
			elseif($k <= 19)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
			}
			elseif($k <= 22)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
			}
			else
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
			}
		}
		$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
		$writer->addRow($rowFromValues);

		$subHeaderArray	= ['id','empleado','fecha_de_ingreso','sd','sdi','dias_trabajados','sueldo_total','ptu_por_dias','ptu_por_sueldos','ptu_total','forma_pago','alias','banco','clabe','cuenta','tarjeta','sucursal','ptu_exenta','ptu_gravada','total_de_percepciones','isr','pension_alimenticia','total_de_retenciones','sueldo_neto' ];
		
		$tempHeaders	= [];
		foreach($subHeaderArray as $k => $subHeader)
		{
			if($k <= 16)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol1);
			}
			elseif($k <= 19)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
			}
			elseif($k <= 22)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
			}
			else
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol4);
			}
		}
		$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
		$writer->addRow($rowFromValues);
		$tempIdrealEmployee	= '';
		$kindRow			= true;
		$flagAlimony 		= false;
		foreach($nominaEmployees as $nomina_employee)
		{
			if($tempIdrealEmployee != $nomina_employee->idrealEmployee)
			{
				$tempIdrealEmployee = $nomina_employee->idrealEmployee;
				$kindRow = !$kindRow;
				
			}
			$tmpArr = [];
			foreach($nomina_employee as $k => $n)
			{
				if (!in_array($k,['alimony_idrealEmployee','alimony_name','alimony_beneficiary','alimony_alias','alimony_bank','alimony_clabe','alimony_account','alimony_cardNumber','alimony_branch','alimony_amount'])) 
				{
					$tmpArr[] = WriterEntityFactory::createCell($n);
				}
				else
				{
					if ($k == 'alimony_amount') 
					{
						if ($n != "" && $n > 0) 
						{
							$flagAlimony = true;
						}
					}
				}
			}
			if($kindRow)
			{
				$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
			}
			else
			{
				$rowFromValues = WriterEntityFactory::createRow($tmpArr);
			}
			$writer->addRow($rowFromValues);
		}

		if ($flagAlimony) 
		{
			$writer->addNewSheetAndMakeItCurrent();
			$writer->getCurrentSheet()->setName('Pensión alimenticia');

			$headerArray	= ['INFORMACIÓN DE PENSION ALIMENTICIA','','','','','','','','',''];
			$tempHeaders		= [];
			foreach($headerArray as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			$subHeaderArray	= ['id','empleado','beneficiario','alias','banco','clabe','cuenta','tarjeta','sucursal','total',];
			
			$tempHeaders	= [];
			foreach($subHeaderArray as $k => $subHeader)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$shStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$tempIdrealEmployee	= '';
			$kindRow			= true;
			$flagAlimony 		= false;
			foreach($nominaEmployees as $nomina_employee)
			{
				if($tempIdrealEmployee != $nomina_employee->alimony_idrealEmployee)
				{
					$tempIdrealEmployee = $nomina_employee->alimony_idrealEmployee;
					$kindRow = !$kindRow;
				}
				$tmpArr = [];
				foreach($nomina_employee as $k => $n)
				{
					if (in_array($k,['alimony_idrealEmployee','alimony_name','alimony_beneficiary','alimony_alias','alimony_bank','alimony_clabe','alimony_account','alimony_cardNumber','alimony_branch','alimony_amount'])) 
					{
						$tmpArr[] = WriterEntityFactory::createCell($n);
					}
				}
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr);
				}
				$writer->addRow($rowFromValues);
			}
		}
		return $writer->close();
	}

	public function exportNom35($id)
	{
		if(Auth::user()->module->whereIn('id',[166,167,168])->count()>0)
		{
			$flagSalary = false;
			$requestModel = App\RequestModel::find($id);
			if ($requestModel->nominasReal->first()->idCatTypePayroll == "001") 
			{
				$flagSalary = true;
			}
			else
			{
				$flagSalary = false;
			}


			if ($flagSalary) 
			{		
				$selectRaw 	= '
								nomina_employees.idrealEmployee as idrealEmployee,
								CONCAT_WS(" ", real_employees.last_name, real_employees.scnd_last_name, real_employees.name) as name,
								real_employees.curp as curp,
								projects.proyectName as projectName,
								enterprises.name as enterpriseName,
								worker_datas.sdi as sdi,
								IF(nomina_employees.idCatPeriodicity IS NOT NULL, nomina_emp_periodicity.description,
									IF(worker_datas.periodicity IS NOT NULL, worker_data_periodicity.description,
										IF(nominas.idCatPeriodicity IS NOT NULL, nominas_periodicity.description, "")
									)
								) as periodicity,
								worker_datas.netIncome as netIncome,
								worker_datas.complement as complement,
								payment_methods.method as paymentMethod,
								employee_accounts.alias as alias,
								cat_banks.description as bank,
								employee_accounts.clabe as clabe,
								employee_accounts.account as account,
								employee_accounts.cardNumber as cardNumber,
								employee_accounts.branch as branch,
								IF(nomina_employees.idCatPeriodicity IS NOT NULL, (nomina_emp_periodicity.days - IFNULL(nomina_employees.absence,0)),
									IF(worker_datas.periodicity IS NOT NULL, (worker_data_periodicity.days - IFNULL(nomina_employees.absence,0)),
										IF(nominas.idCatPeriodicity IS NOT NULL, (nominas_periodicity.days - IFNULL(nomina_employees.absence,0)), "")
									)
								) as workedDays,
								IFNULL(nomina_employees.absence,0) as absence,
								IFNULL(nomina_employees.extra_hours,0) as extra_hours,
								IFNULL(nomina_employees.holidays,0) as holidays,
								IFNULL(nomina_employees.sundays,0) as sundays,
								nomina_employee_n_fs.reference as reference,
								nomina_employee_n_fs.reasonAmount as reasonAmount,
								IFNULL(discounts_nominas.discounts, 0) as discounts,
								IFNULL(extras_nominas.extras, 0) as extras,
								ROUND(IF(nominas.idCatTypePayroll = "001" AND nomina_employees_fiscal.fiscal = 1, IFNULL(salaries.infonavit,0), 0),2) AS infonavit_fiscal,
								ROUND(IF(nominas.idCatTypePayroll = "001" AND nomina_employees_fiscal.fiscal = 1, IFNULL(salaries.fonacot,0), 0),2) AS fonacot_fiscal,
								ROUND(IF(nominas.idCatTypePayroll = "001" AND nomina_employees_fiscal.fiscal = 1, IFNULL(salaries.loan_retention,0), 0),2) AS loan_retention_fiscal,
								ROUND(
									IF(nominas.idCatTypePayroll = "001" AND nomina_employees_fiscal.fiscal = 1, IFNULL(salaries.netIncome,0), 
										IF(nominas.idCatTypePayroll = "002" AND nomina_employees_fiscal.fiscal = 1, IFNULL(bonuses.netIncome,0), 
											IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees_fiscal.fiscal = 1, IFNULL(liquidations.netIncome,0), 
												IF(nominas.idCatTypePayroll = "005" AND nomina_employees_fiscal.fiscal = 1, IFNULL(vacation_premia.netIncome,0), 
													IF(nominas.idCatTypePayroll = "006" AND nomina_employees_fiscal.fiscal = 1, IFNULL(profit_sharings.netIncome,0), 0
													)
												)
											)
										)
								),2) AS netIncome_fiscal,
								ROUND(IFNULL(discounts_nominas_infonavit.discounts, 0),2) as discounts_infonavit,
								ROUND(IFNULL(discounts_nominas_fonacot.discounts, 0),2) as discounts_fonacot,
								nomina_employee_n_fs.extra_time as extra_time_nf,
								nomina_employee_n_fs.holiday as holiday_nf,
								nomina_employee_n_fs.sundays as sundays_nf,
								nomina_employee_n_fs.complementPartial as complementPartial_nf,
								nomina_employee_n_fs.amount as amount_nf,
								(ROUND(
									IF(nominas.idCatTypePayroll = "001" AND nomina_employees_fiscal.fiscal = 1, IFNULL(salaries.netIncome,0), 
										IF(nominas.idCatTypePayroll = "002" AND nomina_employees_fiscal.fiscal = 1, IFNULL(bonuses.netIncome,0), 
											IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees_fiscal.fiscal = 1, IFNULL(liquidations.netIncome,0), 
												IF(nominas.idCatTypePayroll = "005" AND nomina_employees_fiscal.fiscal = 1, IFNULL(vacation_premia.netIncome,0), 
													IF(nominas.idCatTypePayroll = "006" AND nomina_employees_fiscal.fiscal = 1, IFNULL(profit_sharings.netIncome,0), 0
													)
												)
											)
										)
								),2) 
									+ ROUND(IF(nominas.idCatTypePayroll = "001" AND nomina_employees_fiscal.fiscal = 1, IFNULL(salaries.infonavit,0), 0),2) 
									+ ROUND(IF(nominas.idCatTypePayroll = "001" AND nomina_employees_fiscal.fiscal = 1, IFNULL(salaries.fonacot,0), 0),2) 
									+ ROUND(IF(nominas.idCatTypePayroll = "001" AND nomina_employees_fiscal.fiscal = 1, IFNULL(salaries.loan_retention,0), 0),2) 
									+ ROUND(nomina_employee_n_fs.amount,2)) as neto_total
							';
			}
			else
			{
				$selectRaw 	= '
								nomina_employees.idrealEmployee as idrealEmployee,
								CONCAT_WS(" ", real_employees.last_name, real_employees.scnd_last_name, real_employees.name) as name,
								real_employees.curp as curp,
								projects.proyectName as projectName,
								enterprises.name as enterpriseName,
								worker_datas.sdi as sdi,
								IF(nomina_employees.idCatPeriodicity IS NOT NULL, nomina_emp_periodicity.description,
									IF(worker_datas.periodicity IS NOT NULL, worker_data_periodicity.description,
										IF(nominas.idCatPeriodicity IS NOT NULL, nominas_periodicity.description, "")
									)
								) as periodicity,
								worker_datas.netIncome as netIncome,
								worker_datas.complement as complement,
								payment_methods.method as paymentMethod,
								employee_accounts.alias as alias,
								cat_banks.description as bank,
								employee_accounts.clabe as clabe,
								employee_accounts.account as account,
								employee_accounts.cardNumber as cardNumber,
								employee_accounts.branch as branch,
								nomina_employee_n_fs.reference as reference,
								nomina_employee_n_fs.reasonAmount as reasonAmount,
								ROUND(
									IF(nominas.idCatTypePayroll = "001" AND nomina_employees_fiscal.fiscal = 1, IFNULL(salaries.netIncome,0), 
										IF(nominas.idCatTypePayroll = "002" AND nomina_employees_fiscal.fiscal = 1, IFNULL(bonuses.netIncome,0), 
											IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees_fiscal.fiscal = 1, IFNULL(liquidations.netIncome,0), 
												IF(nominas.idCatTypePayroll = "005" AND nomina_employees_fiscal.fiscal = 1, IFNULL(vacation_premia.netIncome,0), 
													IF(nominas.idCatTypePayroll = "006" AND nomina_employees_fiscal.fiscal = 1, IFNULL(profit_sharings.netIncome,0), 0
													)
												)
											)
										)
								),2) AS netIncome_fiscal,
								IFNULL(discounts_nominas.discounts, 0) as discounts,
								IFNULL(extras_nominas.extras, 0) as extras,
								nomina_employee_n_fs.amount as amount_nf,
								(ROUND(
									IF(nominas.idCatTypePayroll = "001" AND nomina_employees_fiscal.fiscal = 1, IFNULL(salaries.netIncome,0), 
										IF(nominas.idCatTypePayroll = "002" AND nomina_employees_fiscal.fiscal = 1, IFNULL(bonuses.netIncome,0), 
											IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees_fiscal.fiscal = 1, IFNULL(liquidations.netIncome,0), 
												IF(nominas.idCatTypePayroll = "005" AND nomina_employees_fiscal.fiscal = 1, IFNULL(vacation_premia.netIncome,0), 
													IF(nominas.idCatTypePayroll = "006" AND nomina_employees_fiscal.fiscal = 1, IFNULL(profit_sharings.netIncome,0), 0
													)
												)
											)
										)
								),2) + ROUND(nomina_employee_n_fs.amount,2)) as neto_total
							';
			}
			$nominaEmployees = DB::table('request_models')
								->selectRaw($selectRaw)
								->leftJoin('nominas','nominas.idFolio','request_models.folio')
								->leftJoin('cat_periodicities as nominas_periodicity','nominas_periodicity.c_periodicity','nominas.idCatPeriodicity')
								->leftJoin('nomina_employees','nomina_employees.idnomina','nominas.idnomina')
								->leftJoin('cat_periodicities as nomina_emp_periodicity','nomina_emp_periodicity.c_periodicity','nomina_employees.idCatPeriodicity')
								->leftJoin('real_employees','real_employees.id','nomina_employees.idrealEmployee')
								->leftJoin('worker_datas','worker_datas.id','nomina_employees.idWorkingData')
								->leftJoin('enterprises','enterprises.id','worker_datas.enterprise')
								->leftJoin('projects','projects.idproyect','worker_datas.project')
								->leftJoin('cat_periodicities as worker_data_periodicity','worker_data_periodicity.c_periodicity','worker_datas.periodicity')
								->leftJoin('nomina_employee_n_fs','nomina_employee_n_fs.idnominaEmployee','nomina_employees.idnominaEmployee')
								->leftJoin('payment_methods','payment_methods.idpaymentMethod','nomina_employee_n_fs.idpaymentMethod')
								->leftJoin('employee_accounts','employee_accounts.id','nomina_employee_n_fs.idemployeeAccounts')
								->leftJoin('cat_banks','cat_banks.c_bank','employee_accounts.idCatBank')
								->leftJoin(DB::raw('(SELECT idnominaemployeenf, SUM(amount) AS discounts FROM discounts_nominas  WHERE reason NOT LIKE "%INFONAVIT%" AND reason NOT LIKE "%FONACOT%" GROUP BY idnominaemployeenf) as discounts_nominas'),'discounts_nominas.idnominaemployeenf','nomina_employee_n_fs.idnominaemployeenf')
								->leftJoin(DB::raw('(SELECT idnominaemployeenf, SUM(amount) AS discounts FROM discounts_nominas  WHERE reason LIKE "%INFONAVIT%" GROUP BY idnominaemployeenf) as discounts_nominas_infonavit'),'discounts_nominas_infonavit.idnominaemployeenf','nomina_employee_n_fs.idnominaemployeenf')
								->leftJoin(DB::raw('(SELECT idnominaemployeenf, SUM(amount) AS discounts FROM discounts_nominas  WHERE reason LIKE "%FONACOT%" GROUP BY idnominaemployeenf) as discounts_nominas_fonacot'),'discounts_nominas_fonacot.idnominaemployeenf','nomina_employee_n_fs.idnominaemployeenf')
								->leftJoin(DB::raw('(SELECT idnominaemployeenf, SUM(amount) AS extras FROM extras_nominas GROUP BY idnominaemployeenf) as extras_nominas'),'extras_nominas.idnominaemployeenf','nomina_employee_n_fs.idnominaemployeenf')
								->leftJoin('request_models as request_fiscal', function($join)
								{
									$join->on('request_fiscal.idprenomina','request_models.idprenomina')
										->on('request_fiscal.idDepartment','request_models.idDepartment')
										->where('request_fiscal.taxPayment',1)
										->where('request_fiscal.kind',16);
								})
								->leftJoin('nominas as nominas_fiscal','nominas_fiscal.idFolio','request_fiscal.folio')
								->leftJoin('nomina_employees as nomina_employees_fiscal',function($join)
								{
									$join->on('nomina_employees_fiscal.idrealEmployee','nomina_employees.idrealEmployee')
										->on('nomina_employees_fiscal.idnomina','nominas_fiscal.idnomina');
								})
								->leftJoin('salaries','salaries.idnominaEmployee','nomina_employees_fiscal.idnominaEmployee')
								->leftJoin('bonuses','bonuses.idnominaEmployee','nomina_employees_fiscal.idnominaEmployee')
								->leftJoin('settlements','settlements.idnominaEmployee','nomina_employees_fiscal.idnominaEmployee')
								->leftJoin('liquidations','liquidations.idnominaEmployee','nomina_employees_fiscal.idnominaEmployee')
								->leftJoin('vacation_premia','vacation_premia.idnominaEmployee','nomina_employees_fiscal.idnominaEmployee')
								->leftJoin('profit_sharings','profit_sharings.idnominaEmployee','nomina_employees_fiscal.idnominaEmployee')
								->where('request_models.folio',$id)
								->get();
			$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat	= (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('343a40')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('f8cd5c')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('7fc544')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol4	= (new StyleBuilder())->setBackgroundColor('EE881F')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol5	= (new StyleBuilder())->setBackgroundColor('ffffff')->setFontColor(Color::BLACK)->setFontBold()->build();
			$mhStyleCol6	= (new StyleBuilder())->setBackgroundColor('fca700')->setFontColor(Color::WHITE)->setFontBold()->build();
			$writer			= WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('NOM035_'.$id.'.xlsx');
			if ($flagSalary) 
			{
				$headerArray	= ['nomina','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
			}
			else
			{
				$headerArray	= ['nomina','','','','','','','','','','','','','','','','','','','','','',''];
			}
			$tempHeaders		= [];
			foreach($headerArray as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			if ($flagSalary) 
			{
				$headerArray	= ['informacion_general','','','','','','','','','datos_de_forma_pago','','','','','','','datos_del_pago','','','','','','','','','','','','','','','','','','','',''];
			}
			else
			{
				$headerArray	= ['informacion_general','','','','','','','','','datos_de_forma_pago','','','','','','','datos_del_pago','','','','','',''];
			}
			$tempHeaders		= [];
			foreach($headerArray as $k => $header)
			{
				if($k <= 8)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
				}
				elseif($k <= 15)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
				}
				else
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			if ($flagSalary) 
			{
				$subHeaderArray	= ['id','empleado','curp','proyecto', 'empresa','sdi', 'periodicidad', 'sueldo_neto','complemento', 'forma_de_pago','alias','banco','clabe','cuenta','tarjeta','sucursal','dias_trabajados','faltas','horas_extra','dias_festivos','domingos_trabajados','referencia','razon_de_pago','descuento','extra','infonavit_fiscal','fonacot_fiscal','prestamo_fiscal','sueldo_neto_fiscal','retencion_infonavit','retencion_fonacot','total_horas_extra','total_dias_festivos','total_domingos_trabajados','sueldo_neto_no_fiscal','total_no_fiscal_por_pagar','neto_total'];
			}
			else
			{
				$subHeaderArray	= ['id','empleado','curp','proyecto','empresa','sdi','periodicidad','sueldo_neto','complemento', 'forma_de_pago','alias','banco', 'clabe', 'cuenta', 'tarjeta', 'sucursal', 'referencia','razon_de_pago', 'total_fiscal_pagado','descuento', 'extra', 'total_no_fiscal_por_pagar','neto_total'];
			}
			$tempHeaders	= [];
			foreach($subHeaderArray as $k => $subHeader)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol5);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			$tempIdrealEmployee     = '';
			$kindRow       = true;
			foreach($nominaEmployees as $nomina_employee)
			{
				if($tempIdrealEmployee != $nomina_employee->idrealEmployee)
				{
					$tempIdrealEmployee = $nomina_employee->idrealEmployee;
					$kindRow = !$kindRow;
					
				}
				$tmpArr = [];
				foreach($nomina_employee as $k => $r)
				{
					if(in_array($k,['discounts','extras','infonavit_fiscal','fonacot_fiscal','loan_retention_fiscal','netIncome_fiscal','discounts_infonavit','discounts_fonacot','extra_time_nf','holiday_nf','sundays_nf','complementPartial_nf','neto_total']))
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}
					elseif($k == 'amount_nf')
					{
						$tmpArr[] = WriterEntityFactory::createCell((double)$r,$mhStyleCol6);
					}
					else
					{
						$tmpArr[] = WriterEntityFactory::createCell($r);
					}
				}
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr);
				}
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
		}
		else
		{
			return redirect('/');
		}
	}


	public function days_count($month,$year,$dayNumber)
	{
		// 1 - lunes
		// 2 - martes
		// 3- miercoles
		// ..
		// 7 - domingo
		$count        = 0;
		$dias_month	= cal_days_in_month(CAL_GREGORIAN, $month, $year);
		for($i=1;$i<=$dias_month;$i++)
		if(date('N',strtotime($year.'-'.$month.'-'.$i))==$dayNumber)
		$count++;
		return $count;
	}

	public function days_month($month,$year)
	{
		$count = cal_days_in_month(CAL_GREGORIAN, $month, $year);
		return $count;
	}

	public function days_bimester($date)
	{
		$d = new DateTime($date);
		switch ($d->format('m'))
		{
			case 1:
			case 2:
				$one	= new DateTime($d->format('Y').'-01-01');
				$two	= new DateTime($d->format('Y').'-02-01');
				break;
			case 3:
			case 4:
				$one	= new DateTime($d->format('Y').'-03-01');
				$two	= new DateTime($d->format('Y').'-04-01');
				break;
			case 5:
			case 6:
				$one	= new DateTime($d->format('Y').'-05-01');
				$two	= new DateTime($d->format('Y').'-06-01');
				break;
			case 7:
			case 8:
				$one	= new DateTime($d->format('Y').'-07-01');
				$two	= new DateTime($d->format('Y').'-08-01');
				break;
			case 9:
			case 10:
				$one	= new DateTime($d->format('Y').'-09-01');
				$two	= new DateTime($d->format('Y').'-10-01');
				break;
			case 11:
			case 12:
				$one	= new DateTime($d->format('Y').'-11-01');
				$two	= new DateTime($d->format('Y').'-12-01');
				break;
		}
		return $one->format('t') + $two->format('t');
	}

	public function pay_infonavit($start,$ending)
	{
		$toPay		= 0;
		$one		= new DateTime($start);
		$two		= new DateTime($ending);
		switch ($one->format('m'))
		{
			case 1:
			case 2:
				$x			= new DateTime($one->format('Y').'-02-01');
				break;
			case 3:
			case 4:
				$x			= new DateTime($one->format('Y').'-04-01');
				break;
			case 5:
			case 6:
				$x			= new DateTime($one->format('Y').'-06-01');
				break;
			case 7:
			case 8:
				$x			= new DateTime($one->format('Y').'-08-01');
				break;
			case 9:
			case 10:
				$x			= new DateTime($one->format('Y').'-10-01');
				break;
			case 11:
			case 12:
				$x			= new DateTime($one->format('Y').'-12-01');
				break;
		}
		$compare	= new DateTime($x->format('Y-m-t'));
		if($one <= $compare && $compare <= $two)
		{
			$toPay	= 15;
		}
		return $toPay;
	}

	public function daysPassed($startDate,$endDate)
	{
		$days = (strtotime($startDate)-strtotime($endDate))/86400;
		$days = abs($days); $days = floor($days);
		return $days;
	}

	public function recalculateNomina($typePayroll,$idnominaemployee)
	{
		$daysYear = date("L") == 1 ? 366 : 365;
		switch ($typePayroll) 
		{
			case '001':
				$t_nominaemployee					= App\NominaEmployee::find($idnominaemployee);
				$calculations = [];
				//calculo para dias de vacaciones
				$calculations['admissionDate']  = $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
				$calculations['nowDate']        = Carbon::now();
				$calculations['diasTrabajados'] = App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['admissionDate'],$calculations['nowDate']);
				$calculations['yearsWork']      = ceil($calculations['diasTrabajados']/365);
				if($calculations['yearsWork'] > 24)
				{
					$calculations['vacationDays']	= 20;
				}
				else
				{
					$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
				}

				//-------------------

				$calculations['prima_vac_esp'] = App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
				$calculations['sdi']           = $t_nominaemployee->workerData->first()->sdi;
				$calculations['sd']            = round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);
				$daysStart                     = 0;
				if (new \DateTime($t_nominaemployee->from_date) < new \DateTime($calculations['admissionDate'])) 
				{
					$datetime1 = date_create($t_nominaemployee->from_date);
					$datetime2 = date_create($calculations['admissionDate']);
					$interval  = date_diff($datetime1, $datetime2);
					$daysStart = $interval->format('%a');
				}
				else
				{
					$daysStart = 0;
				}
				$downDate = $t_nominaemployee->workerData->first()->downDate != '' && new \DateTime($t_nominaemployee->workerData->first()->downDate) > new \DateTime($calculations['admissionDate']) ? $t_nominaemployee->workerData->first()->downDate : null;
				$daysDown = 0;
				if ($downDate !='' && new \DateTime($downDate) >= new \DateTime($t_nominaemployee->from_date) && new \DateTime($downDate) <= new \DateTime($t_nominaemployee->to_date)) 
				{
					$date1    = new \DateTime($downDate);
					$date2    = new \DateTime($t_nominaemployee->to_date);
					$diff     = $date1->diff($date2);
					$daysDown = $diff->days;
				}
				else
				{
					$daysDown = 0;
				}
				$calculations['workedDays']  = (App\CatPeriodicity::find($t_nominaemployee->idCatPeriodicity)->days)-$t_nominaemployee->absence-$daysStart-$daysDown;
				$calculations['periodicity'] = App\CatPeriodicity::find($t_nominaemployee->idCatPeriodicity)->description;
				$calculations['rangeDate']   = $t_nominaemployee->from_date.' '.$t_nominaemployee->to_date;
				switch ($t_nominaemployee->idCatPeriodicity) 
				{
					case '02':
						$d = new DateTime($t_nominaemployee->from_date);
						$d->modify('next thursday');
						$calculations['divisorDayFormImss'] = App\Http\Controllers\AdministracionNominaController::days_count($d->format('m'),$d->format('Y'),4);
						break;
					case '04':
						$calculations['divisorDayFormImss'] = 2;
						break;
					case '05':
						$calculations['divisorDayFormImss'] = 1;
						break;
				}
				$d = new DateTime($t_nominaemployee->from_date);
				$d->modify('next thursday');
				$calculations['daysMonth'] = App\Http\Controllers\AdministracionNominaController::days_month($d->format('m'),$d->format('Y'));
				if ($t_nominaemployee->WorkerData->first()->regime_id == '09') 
				{
					$calculations['daysForImss'] = 0;
				}
				else
				{
					switch ($t_nominaemployee->idCatPeriodicity) 
					{
						case '02':
							if($calculations['workedDays'] < 7)
							{
								$calculations['daysForImss'] = $calculations['workedDays'];
							}
							else
							{
								$calculations['daysForImss'] = $calculations['daysMonth']/$calculations['divisorDayFormImss'];
							}
							break;

						case '04':
							if($calculations['workedDays'] < 15)
							{
								$calculations['daysForImss'] = $calculations['workedDays'];
							}
							else
							{
								$calculations['daysForImss'] = $calculations['daysMonth']/$calculations['divisorDayFormImss'];
							}
							break;

						case '05':
							if($calculations['workedDays'] < 30)
							{
								$calculations['daysForImss'] = $calculations['workedDays'];
							}
							else
							{
								$calculations['daysForImss'] = $calculations['daysMonth']/$calculations['divisorDayFormImss'];
							}
							break;
					}
				}
				//TIEMPO EXTRA Y DÍAS FESTIVOS
				$calculations['uma']        = App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
				$calculations['extra_time'] = $t_nominaemployee->extra_hours < 9 ? ($calculations['sd'] / 8 * 2 * $t_nominaemployee->extra_hours) : (($calculations['sd'] / 8 * 2 * 9) + ($calculations['sd'] / 8 * 3 * ($t_nominaemployee->extra_hours - 9)));
				$calculations['holiday']    = $t_nominaemployee->holidays * $calculations['sd'] * 2;
				$calculations['sunday_bonus']  = $calculations['sd'] * .25 * $t_nominaemployee->sundays;
				$calculations['sunday_except'] = $calculations['sunday_bonus'] > $calculations['uma'] ? $calculations['uma'] : $calculations['sunday_bonus'];
				$calculations['sunday_taxed']  = $calculations['sunday_bonus'] > $calculations['uma'] ? ($calculations['sunday_bonus'] - $calculations['sunday_except']) : 0;

				//PERCEPCIONES
				$calculations['salary']         = $calculations['sd']*$calculations['workedDays'];
				$calculations['loanPerception'] = $t_nominaemployee->loan_perception;
				$calculations['puntuality']     = $calculations['salary'] * (($t_nominaemployee->workerData->first()->bono/100)/2);
				$calculations['assistance']     = $calculations['salary'] * (($t_nominaemployee->workerData->first()->bono/100)/2);
				$parameterMinimumSalary         = App\Parameter::where('parameter_name','SM')->first()->parameter_value;
				$calculations['extra_hours']    = $calculations['sd'] == $parameterMinimumSalary ? 0 : ($calculations['extra_time'] > (5 * $calculations['uma']) ? ($calculations['extra_time'] - ($calculations['uma'] * 5)) : ($t_nominaemployee->extra_hours > 9 ? (($t_nominaemployee->extra_hours - 9) * $calculations['sd'] / 8 * 3) : ($calculations['extra_time'] * .5)));
				$calculations['holidays']       = $calculations['sd'] == $parameterMinimumSalary ? 0 : ($calculations['holiday'] > (5 * $calculations['uma']) ? ($calculations['holiday'] - ($calculations['uma'] * 5)) : ($calculations['holiday'] * .5));

				//calculo para el subsidio
				$calculations['baseTotalDePercepciones'] = $calculations['salary'] + $calculations['puntuality'] + $calculations['assistance'] + $calculations['extra_hours'] + $calculations['holidays'] + $calculations['sunday_taxed'];
				$calculations['baseISR']                 = ($calculations['baseTotalDePercepciones']/$calculations['workedDays'])*30.4;
				$parameterISR                            = App\ParameterISR::where('inferior','<=',$calculations['baseISR'])->where('lapse',30)->get();
				$calculations['limiteInferior']          = $parameterISR->last()->inferior;
				$calculations['excedente']               = $calculations['baseISR']-$calculations['limiteInferior'];
				$calculations['factor']                  = $parameterISR->last()->excess/100;
				$calculations['isrMarginal']             = $calculations['excedente'] * $calculations['factor'];
				$calculations['cuotaFija']               = $parameterISR->last()->quota;
				$calculations['isrAntesDelSubsidio']     = (($calculations['isrMarginal'] + $calculations['cuotaFija'])/30.4)*$calculations['workedDays'];
				$parameterSubsidy                        = App\ParameterSubsidy::where('inferior','<=',$calculations['baseISR'])->where('lapse',30)->get();
				if ($calculations['baseISR'] <= 7382.34)
				{
					$calculations['subsidioAlEmpleo'] = ($parameterSubsidy->last()->subsidy/30.4)*$calculations['workedDays'];
				}
				else
				{
					$calculations['subsidioAlEmpleo'] = 0;
				}
				if(($calculations['isrAntesDelSubsidio'] - $calculations['subsidioAlEmpleo']) > 0)
				{
					$calculations['isrARetener'] = $calculations['isrAntesDelSubsidio'] - $calculations['subsidioAlEmpleo'];
					$calculations['subsidio']    = 0;
				}
				else
				{
					$calculations['isrARetener'] = 0;
					$calculations['subsidio']    = round(($calculations['isrAntesDelSubsidio'] - $calculations['subsidioAlEmpleo'])*(-1),2); 	
				}
				$calculations['totalPerceptions']	= round(round($calculations['salary'],2) + round($calculations['loanPerception'],2) + round($calculations['puntuality'],2) + round($calculations['assistance'],2) + round($calculations['subsidio'],2) + round($calculations['extra_time'],2) + round($calculations['holiday'],2) + round($calculations['sunday_bonus'],2),2);
				
				//----------------------------

				//RETENCIONES

				// calculo de IMSS (cuotas obrero-patronal)
				$calculations['SalarioBaseDeCotizacion']	= $calculations['sdi'];
				$calculations['diasDelPeriodoMensual']		= $calculations['daysForImss'];
				$calculations['diasDelPeriodoBimestral']	= $calculations['daysForImss'];
				$calculations['primaDeRiesgoDeTrabajo']		= App\EmployerRegister::where('employer_register',$t_nominaemployee->workerData->first()->employer_register)->first()->risk_number; 
				
				if (($calculations['uma']*3) > $calculations['SalarioBaseDeCotizacion'])
				{
					$calculations['imssExcedente'] 			= 0;
				}
				else
				{
					$calculations['imssExcedente']			= ((($calculations['SalarioBaseDeCotizacion']-(3*$calculations['uma']))*$calculations['diasDelPeriodoMensual'])*0.4)/100;
				}
				$calculations['prestacionesEnDinero']		= (($calculations['SalarioBaseDeCotizacion']*$calculations['diasDelPeriodoMensual'])*0.25)/100;
				$calculations['gastosMedicosPensionados']	= (($calculations['SalarioBaseDeCotizacion']*$calculations['diasDelPeriodoMensual'])*0.375)/100;
				$calculations['invalidezVidaPatronal']		= (($calculations['SalarioBaseDeCotizacion']*$calculations['diasDelPeriodoMensual'])*0.625)/100;
				$calculations['cesantiaVejez']				= (($calculations['SalarioBaseDeCotizacion']*$calculations['diasDelPeriodoMensual'])*1.125)/100;

				$calculations['imss'] = $calculations['imssExcedente']+$calculations['prestacionesEnDinero']+$calculations['gastosMedicosPensionados']+$calculations['invalidezVidaPatronal']+$calculations['cesantiaVejez'];

				//calculo infonavit

				$calculations['diasBimestre']		= App\Http\Controllers\AdministracionNominaController::days_bimester($t_nominaemployee->from_date);
				$calculations['factorInfonavit']	= App\Parameter::where('parameter_name','INFONAVIT_FACTOR')->first()->parameter_value;

				if ($t_nominaemployee->workerData->first()->infonavitDiscountType != '') 
				{
					$calculations['descuentoEmpleado']	= $t_nominaemployee->workerData->first()->infonavitDiscount;
					$calculations['quinceBimestral']	= App\Http\Controllers\AdministracionNominaController::pay_infonavit($t_nominaemployee->from_date,$t_nominaemployee->to_date);
					switch ($t_nominaemployee->workerData->first()->infonavitDiscountType) 
					{
						case 1:
							$calculations['descuentoInfonavitTemp'] = (($calculations['descuentoEmpleado']*$calculations['factorInfonavit']*2)/$calculations['diasBimestre'])*$calculations['daysForImss']+$calculations['quinceBimestral']; 
							break;

						case 2:
							$calculations['descuentoInfonavitTemp'] = $calculations['descuentoEmpleado']*2/$calculations['diasBimestre']*$calculations['daysForImss']+$calculations['quinceBimestral']; 
							break;

						case 3:
							$calculations['descuentoInfonavitTemp'] = (($calculations['sdi']*($calculations['descuentoEmpleado']/100)*$calculations['daysForImss']))+$calculations['quinceBimestral']; 
							break;
					}
				}
				else
				{
					$calculations['descuentoInfonavitTemp'] = 0 ;
				}

				// -------------------

				$calculations['fonacot']               = (($t_nominaemployee->workerData->first()->fonacot/30.4)*$calculations['daysForImss']);
				$calculations['loanRetention']         = $t_nominaemployee->loan_retention;
				$calculations['otherRetentionConcept'] = $t_nominaemployee->salary->first()->other_retention_concept;
				$calculations['otherRetentionAmount']  = $t_nominaemployee->salary->first()->other_retention_amount;
				$calculations['totalRetentionsTemp']   = round(round($calculations['imss'],2)+round($calculations['descuentoInfonavitTemp'],2)+round($calculations['fonacot'],2)+round($calculations['loanRetention'],2)+round($calculations['isrARetener'],2)+round($calculations['otherRetentionAmount'],2),2);
				$calculations['percentage']            = ($calculations['totalRetentionsTemp'] * 100) / $calculations['salary'];
				if($calculations['percentage'] > 80)
				{
					$calculations['descuentoInfonavit']            = 0 ;
					$calculations['descuentoInfonavitComplemento'] = $calculations['descuentoInfonavitTemp'];
				}
				else
				{
					$calculations['descuentoInfonavit']            = $calculations['descuentoInfonavitTemp'];
					$calculations['descuentoInfonavitComplemento'] = 0;
				}
				//pensión alimenticia

				if ($t_nominaemployee->workerData->first()->alimonyDiscountType != '') 
				{
					$calculations['totalRetentionsTemp'] = round(round($calculations['imss'],2)+round($calculations['descuentoInfonavit'],2)+round($calculations['fonacot'],2)+round($calculations['loanRetention'],2)+round($calculations['isrARetener'],2)+round($calculations['otherRetentionAmount'],2),2);
					$calculations['netIncomeTemp']       = round($calculations['totalPerceptions']-$calculations['totalRetentionsTemp'],2);
					switch ($t_nominaemployee->workerData->first()->alimonyDiscountType)
					{
						case 1: //monto
							$calculations['amountAlimony']	= $t_nominaemployee->workerData->first()->alimonyDiscount;
							$calculations['alimony']		= $calculations['amountAlimony'];
							break;
						case 2: // porcentaje
							$calculations['amountAlimony']	= $t_nominaemployee->workerData->first()->alimonyDiscount;
							$calculations['alimony']		= ($calculations['netIncomeTemp']*$calculations['amountAlimony'])/100;
							break;
					}
					$calculations['totalRetentions'] = round(round($calculations['imss'],2)+round($calculations['descuentoInfonavit'],2)+round($calculations['fonacot'],2)+round($calculations['loanRetention'],2)+round($calculations['isrARetener'],2)+round($calculations['alimony'],2)+round($calculations['otherRetentionAmount'],2),2);
					$calculations['netIncome']       = round($calculations['totalPerceptions']-$calculations['totalRetentions'],2);
				}
				else
				{ 
					$calculations['alimony']         = 0;
					$calculations['totalRetentions'] = round(round($calculations['imss'],2)+round($calculations['descuentoInfonavit'],2)+round($calculations['fonacot'],2)+round($calculations['loanRetention'],2)+round($calculations['isrARetener'],2)+round($calculations['otherRetentionAmount'],2),2);
					$calculations['netIncome']       = round($calculations['totalPerceptions']-$calculations['totalRetentions'],2);
				}

				//return $calculations;
				$t_salary                          = App\Salary::find($t_nominaemployee->salary->first()->idSalary);
				$t_salary->idnominaEmployee        = $t_nominaemployee->idnominaEmployee;
				$t_salary->sd                      = $calculations['sd'];
				$t_salary->sdi                     = $calculations['sdi'];
				$t_salary->workedDays              = $calculations['workedDays'];
				$t_salary->daysForImss             = $calculations['daysForImss'];
				$t_salary->salary                  = $calculations['salary'];
				$t_salary->loan_perception         = $calculations['loanPerception'];
				$t_salary->puntuality              = $calculations['puntuality'];
				$t_salary->assistance              = $calculations['assistance'];
				$t_salary->extra_hours             = $t_nominaemployee->extra_hours;
				$t_salary->extra_time              = $calculations['extra_time'];
				$t_salary->extra_time_taxed        = $calculations['extra_hours'];
				$t_salary->holidays                = $t_nominaemployee->holidays;
				$t_salary->holiday                 = $calculations['holiday'];
				$t_salary->holiday_taxed           = $calculations['holidays'];
				$t_salary->sundays                 = $t_nominaemployee->sundays;
				$t_salary->exempt_sunday           = $calculations['sunday_except'];
				$t_salary->taxed_sunday            = $calculations['sunday_taxed'];
				$t_salary->subsidy                 = $calculations['subsidio'];
				$t_salary->totalPerceptions        = $calculations['totalPerceptions'];
				$t_salary->imss                    = $calculations['imss'];
				$t_salary->infonavit               = $calculations['descuentoInfonavit'];
				$t_salary->infonavitComplement     = $calculations['descuentoInfonavitComplemento'];
				$t_salary->fonacot                 = $calculations['fonacot'];
				$t_salary->loan_retention          = $calculations['loanRetention'];
				$t_salary->other_retention_amount  = $calculations['otherRetentionAmount'];
				$t_salary->other_retention_concept = $calculations['otherRetentionConcept'];
				$t_salary->isrRetentions           = $calculations['isrARetener'];
				$t_salary->alimony                 = $calculations['alimony'];
				$t_salary->totalRetentions         = $calculations['totalRetentions'];
				$t_salary->netIncome               = $calculations['netIncome'];
				$t_salary->subsidyCaused           = $calculations['subsidioAlEmpleo'];
				$t_salary->risk_number             = $calculations['primaDeRiesgoDeTrabajo'];
				$t_salary->uma                     = $calculations['uma'];
				$t_salary->save();
				$calculations = [];
				$totalRequest = 0;
				$t_nomina     = App\Nomina::find($t_nominaemployee->idnomina);
				foreach ($t_nomina->nominaEmployee as $n) 
				{
					$totalRequest += $n->salary->first()->netIncome + $n->salary->first()->alimony;
				}
				$t_nomina->amount			= $totalRequest;
				$t_nomina->save();

				$req			= App\RequestModel::find($t_nomina->idFolio);
				$nom_no_fiscal 	= App\RequestModel::where('kind',16)
								->where('idprenomina',$req->idprenomina)
								->where('idDepartment',$req->idDepartment)
								->where('taxPayment',0)
								->get();
				if($nom_no_fiscal != '')
				{
					foreach ($nom_no_fiscal as $request_nf) 
					{
						$nom_emp_nf = App\NominaEmployee::where('idrealEmployee',$t_nominaemployee->idrealEmployee)
													->where('idnomina',$request_nf->nominasReal->first()->idnomina)
													->first();

						if ($nom_emp_nf != "") 
						{
							if ($request_nf->status == 2) 
							{
								$nom_emp_nf->sundays		= $t_nominaemployee->sundays;;
								$nom_emp_nf->extra_hours	= $t_nominaemployee->extra_hours;
								$nom_emp_nf->holidays		= $t_nominaemployee->sundays;
							}

							$idnominaemployeenf 	= $nom_emp_nf->nominasEmployeeNF()->exists() ? $nom_emp_nf->nominasEmployeeNF->first()->idnominaemployeenf : "";
							
							if ($idnominaemployeenf != "") 
							{
								$nomina_nf 				= App\NominaEmployeeNF::find($idnominaemployeenf);
								$nomina_nf->netIncome 	= $nomina_nf->amount + $t_salary->netIncome;
								$nomina_nf->save();
							}
						}
					}
				}	
				return true;
				
				break;

			case '002':
					$t_nominaemployee					= App\NominaEmployee::find($idnominaemployee);
					$calculationsNetIncome = [];
					//calculo para dias de vacaciones
					$calculationsNetIncome['fechaIngreso']		= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
					$calculationsNetIncome['fechaActual']		= Carbon::now();
					$calculationsNetIncome['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculationsNetIncome['fechaIngreso'],$calculationsNetIncome['fechaActual']);
					$calculationsNetIncome['yearsWork']			= ceil($calculationsNetIncome['diasTrabajados']/$daysYear);
					if ($calculationsNetIncome['yearsWork'] > 24) 
					{
						$calculationsNetIncome['vacationDays']	= 20;
					}
					else
					{
						$calculationsNetIncome['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculationsNetIncome['yearsWork'])->where('toYear','>=',$calculationsNetIncome['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculationsNetIncome['yearsWork'])->where('toYear','>=',$calculationsNetIncome['yearsWork'])->first()->days : 0;
					}

					//-------------------

					$calculationsNetIncome['prima_vac_esp']	= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;

					switch ($t_nominaemployee->idCatPeriodicity) 
					{
						case '02':
							$calculationsNetIncome['divisor'] = 7;
							break;

						case '04':
							$calculationsNetIncome['divisor'] = 15;
							break;

						case '05':
							$d = new DateTime(Carbon::now());
							$calculationsNetIncome['divisor'] = App\Http\Controllers\AdministracionNominaController::days_month($d->format('m'),$d->format('Y'));
							break;
						
						default:
							break;
					}

					$calculationsNetIncome['calc_sdi']							= $t_nominaemployee->total/$calculationsNetIncome['divisor'];
					$calculationsNetIncome['sdi']								= $calculationsNetIncome['calc_sdi'];
					$calculationsNetIncome['sd']								= round($calculationsNetIncome['sdi']/((($calculationsNetIncome['vacationDays']*$calculationsNetIncome['prima_vac_esp'])+15+$daysYear)/$daysYear),2);
					
					$calculationsNetIncome['uma']								= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
					$calculationsNetIncome['exento']							= $calculationsNetIncome['uma']*30; 
					$calculationsNetIncome['diasParaAguinaldo']					= $t_nominaemployee->day_bonus;
					$calculationsNetIncome['parteProporcionalParaAguinaldo']	= round((15*$calculationsNetIncome['diasParaAguinaldo'])/$daysYear,6);


					// PERCEPCIONES AGUINALDO---------------------------------------------------------------------

					if (($calculationsNetIncome['parteProporcionalParaAguinaldo'] * $calculationsNetIncome['sd']) < $calculationsNetIncome['exento']) 
					{
						$calculationsNetIncome['aguinaldoExento'] = $calculationsNetIncome['parteProporcionalParaAguinaldo'] * $calculationsNetIncome['sd'];
					}
					else
					{
						$calculationsNetIncome['aguinaldoExento'] = $calculationsNetIncome['exento'];
					}

					if (($calculationsNetIncome['parteProporcionalParaAguinaldo'] * $calculationsNetIncome['sd']) > $calculationsNetIncome['exento']) 
					{
						$calculationsNetIncome['aguinaldoGravable'] = ($calculationsNetIncome['parteProporcionalParaAguinaldo'] * $calculationsNetIncome['sd'])-$calculationsNetIncome['aguinaldoExento'];
					}
					else
					{
						$calculationsNetIncome['aguinaldoGravable'] = 0;
					}

					$calculationsNetIncome['totalPercepciones'] = round(round($calculationsNetIncome['aguinaldoExento'],2) + round($calculationsNetIncome['aguinaldoGravable'],2),2);
					$calculationsNetIncome['netIncome']			= $calculationsNetIncome['totalPercepciones'];

					$calculations = [];
					//calculo para dias de vacaciones
					$calculations['fechaIngreso']		= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
					$calculations['fechaActual']		= Carbon::now();
					$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
					$calculations['yearsWork']			= ceil($calculations['diasTrabajados']/$daysYear);
					if ($calculations['yearsWork'] > 24) 
					{
						$calculations['vacationDays']	= 20;
					}
					else
					{
						$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
					}

					//-------------------

					$calculations['prima_vac_esp']	= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
					$calculations['sdi']			= $t_nominaemployee->workerData->first()->sdi;
					$calculations['sd']				= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+$daysYear)/$daysYear),2);

					$calculations['uma']							= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
					$calculations['exento']							= $calculations['uma']*30; 
					$calculations['diasParaAguinaldo']				= $t_nominaemployee->day_bonus;
					$calculations['parteProporcionalParaAguinaldo']	= round((15*$calculations['diasParaAguinaldo'])/$daysYear,6);


					// PERCEPCIONES AGUINALDO---------------------------------------------------------------------

					if (($calculations['parteProporcionalParaAguinaldo'] * $calculations['sd']) < $calculations['exento']) 
					{
						$calculations['aguinaldoExento'] = $calculations['parteProporcionalParaAguinaldo'] * $calculations['sd'];
					}
					else
					{
						$calculations['aguinaldoExento'] = $calculations['exento'];
					}

					if (($calculations['parteProporcionalParaAguinaldo'] * $calculations['sd']) > $calculations['exento']) 
					{
						$calculations['aguinaldoGravable'] = ($calculations['parteProporcionalParaAguinaldo'] * $calculations['sd'])-$calculations['aguinaldoExento'];
					}
					else
					{
						$calculations['aguinaldoGravable'] = 0;
					}

					$calculations['totalPercepciones'] = round(round($calculations['aguinaldoExento'],2) + round($calculations['aguinaldoGravable'],2),2);

					// --------------------------------------------------------------------------------------------

					// RETENCIONES- ISR ---------------------------------------------------------------------

					// ISR 1ER FRACCION

					$calculations['baseISR_fraccion1']			= round((($calculations['aguinaldoGravable']/$daysYear)*30.4)+($calculations['sd']*30),6);
					$parameterISRF1								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion1'])->where('lapse',30)->get();

					$calculations['limiteInferior_fraccion1']	= $parameterISRF1->last()->inferior;
					$calculations['excedente_fraccion1']		= round($calculations['baseISR_fraccion1']-$calculations['limiteInferior_fraccion1'],6);
					$calculations['factor_fraccion1']			= round($parameterISRF1->last()->excess/100,6);
					$calculations['isrMarginal_fraccion1']		= round($calculations['excedente_fraccion1'] * $calculations['factor_fraccion1'],6);
					$calculations['cuotaFija_fraccion1']		= round($parameterISRF1->last()->quota,6);
					$calculations['isr_fraccion1']				= round($calculations['isrMarginal_fraccion1']+$calculations['cuotaFija_fraccion1'],6);

					// ISR 2DA FRACCION

					$calculations['baseISR_fraccion2']			= round($calculations['sd']*30,6);
					$parameterISRF2								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion2'])->where('lapse',30)->get();

					$calculations['limiteInferior_fraccion2']	= $parameterISRF2->last()->inferior;
					$calculations['excedente_fraccion2']		= round($calculations['baseISR_fraccion2']-$calculations['limiteInferior_fraccion2'],6);
					$calculations['factor_fraccion2']			= round($parameterISRF2->last()->excess/100,6);
					$calculations['isrMarginal_fraccion2']		= round($calculations['excedente_fraccion2'] * $calculations['factor_fraccion2'],6);
					$calculations['cuotaFija_fraccion2']		= round($parameterISRF2->last()->quota,6);
					$calculations['isr_fraccion2']				= round($calculations['isrMarginal_fraccion2']+$calculations['cuotaFija_fraccion2'],6);

					$calculations['resta']		= round($calculations['isr_fraccion1']-$calculations['isr_fraccion2'],6);
					$calculations['factor1']	= round((($calculations['aguinaldoGravable']/$daysYear) * 30.4),6);
					if($calculations['factor1'] == 0)
					{
						$calculations['factor2']	= 0;
					}
					else
					{
						$calculations['factor2']	= round($calculations['resta']/$calculations['factor1'],6);
					}
					$calculations['isr']		= round($calculations['factor2']*$calculations['aguinaldoGravable'],6);

					//pensión alimenticia

					if ($t_nominaemployee->workerData->first()->alimonyDiscountType != '') 
					{
						$calculations['totalRetencionesTemp'] = round($calculations['isr'],2);
					
						$calculations['netIncomeTemp']			= round($calculations['totalPercepciones']-$calculations['totalRetencionesTemp'],2);

						switch ($t_nominaemployee->workerData->first()->alimonyDiscountType) 
						{
							case 1: //monto
								$calculations['amountAlimony']	= $t_nominaemployee->workerData->first()->alimonyDiscount;
								$calculations['alimony']		= $calculations['amountAlimony'];
								break;

							case 2: // porcentaje
								$calculations['amountAlimony']	= $t_nominaemployee->workerData->first()->alimonyDiscount;
								$calculations['alimony']		= ($calculations['netIncomeTemp']*$calculations['amountAlimony'])/100;
								break;
							default:
								# code...
								break;
						}

						$calculations['totalRetenciones']	= round(round($calculations['isr'],2)+round($calculations['alimony'],2),2);
						$calculations['netIncome']			= round($calculations['totalPercepciones']-$calculations['totalRetenciones'],2);
					}
					else
					{ 
						$calculations['alimony']          = 0;
						$calculations['totalRetenciones'] = round($calculations['isr'],2);
						$calculations['netIncome']        = round($calculations['totalPercepciones']-$calculations['totalRetenciones'],2);
					}


					// --------------------------------------------------------------------------------------------

					$initYear	= date('Y').'-01-01'; 
					$endYear	= date('Y').'-12-31'; 

					$calculations['sueldoDiarioNF'] = $t_nominaemployee->total/$calculationsNetIncome['divisor'];
					$calculations['fechaIngresoNF']	= $t_nominaemployee->workerData->first()->admissionDate->format('Y-m-d');

					if (new \DateTime($calculations['fechaIngresoNF']) < new \DateTime($initYear))
					{
						$calculations['diasTrabajadosNF'] = $daysYear;
					}
					else
					{
						$datetime2	= date_create($endYear);
						$datetime1	= date_create($calculations['fechaIngresoNF']);
						$interval	= date_diff($datetime1, $datetime2);

						$daysDiff = $interval->format('%a');
						$calculations['diasTrabajadosNF'] = $daysDiff+1;
					}

					$calculations['diasParaAguinaldoNF'] = 15 * ($calculations['diasTrabajadosNF']/$daysYear);

					$calculations['sueldoNF'] = round($calculations['diasParaAguinaldoNF'] * $calculations['sueldoDiarioNF'],2);

					
					$t_bonus									= App\Bonus::find($t_nominaemployee->bonus->first()->idBonus);
					$t_bonus->idnominaEmployee					= $t_nominaemployee->idnominaEmployee;
					$t_bonus->sd								= $calculations['sd'];
					$t_bonus->sdi								= $calculations['sdi'];
					$t_bonus->dateOfAdmission					= $calculations['fechaIngreso'];
					$t_bonus->daysForBonuses					= $calculations['diasParaAguinaldo'];
					$t_bonus->proportionalPartForChristmasBonus	= $calculations['parteProporcionalParaAguinaldo'];
					$t_bonus->exemptBonus						= $calculations['aguinaldoExento'];
					$t_bonus->taxableBonus						= $calculations['aguinaldoGravable'];
					$t_bonus->totalPerceptions					= $calculations['totalPercepciones'];
					$t_bonus->isr								= $calculations['isr'];
					$t_bonus->alimony 							= $calculations['alimony'];
					$t_bonus->totalTaxes						= $calculations['totalRetenciones'];
					$t_bonus->netIncome							= $calculations['netIncome'];
					$t_bonus->totalIncomeBonus					= $calculations['sueldoNF'];
					$t_bonus->save();

					$new_total				= round($t_bonus->totalIncomeBonus - $t_bonus->netIncome,2);
					$totalIncomeBonus 		= $t_bonus->totalIncomeBonus;
					$calculations			= [];
					$calculationsNetIncome	= [];
					$totalRequest			= 0;

					$t_nomina				= App\Nomina::find($t_nominaemployee->idnomina);
					foreach ($t_nomina->nominaEmployee as $n) 
					{
						$totalRequest += $n->bonus->first()->netIncome + $n->bonus->first()->alimony;
					}
					$t_nomina->amount = round($totalRequest,2);
					$t_nomina->save();

					$req	= App\RequestModel::find($t_nomina->idFolio);
					$rfn	= App\RequestModel::where('kind',16)
							->where('idprenomina',$req->idprenomina)
							->where('idDepartment',$req->idDepartment)
							->where('taxPayment',0)
							->first();

					if ($rfn != '') 
					{
						$nominaemp = App\NominaEmployee::where('idrealEmployee',$t_nominaemployee->idrealEmployee)
								->where('idnomina',$rfn->nominasReal->first()->idnomina)
								->first();
						if ($nominaemp != '' || $nominaemp != null) 
						{
							$t_nominaemployee_nf = App\NominaEmployeeNF::where('idnominaEmployee',$nominaemp->idnominaEmployee)->first();
							if ($t_nominaemployee_nf != '' || $t_nominaemployee_nf != null) 
							{
								App\DiscountsNomina::where('idnominaemployeenf',$t_nominaemployee_nf->idnominaemployeenf)->delete();
								App\ExtrasNomina::where('idnominaemployeenf',$t_nominaemployee_nf->idnominaemployeenf)->delete();

								$t_nominaemployee_nf->netIncome			= $totalIncomeBonus;
								$t_nominaemployee_nf->complementPartial	= $new_total;
								$t_nominaemployee_nf->amount			= $new_total;
								$t_nominaemployee_nf->save();
							}
						}
					}

					return true;
				break;

			case '003':
			case '004':
			 
				$t_nominaemployee				= App\NominaEmployee::find($idnominaemployee);

				$calculationsNetIncome					= [];

				$calculationsNetIncome['fechaIngreso']	= $t_nominaemployee->workerData->first()->admissionDateOld->format('Y-m-d');
				$calculationsNetIncome['fechaBaja']		= $t_nominaemployee->down_date;
				
				$calculationsNetIncome['fechaActual']	= Carbon::now();
				$calculationsNetIncome['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculationsNetIncome['fechaIngreso'],$calculationsNetIncome['fechaActual']);
				$calculationsNetIncome['añosTrabajados']	= ceil($calculationsNetIncome['diasTrabajados']/365);

				$calculationsNetIncome['diasTrabajadosParaAñosCompletos'] = App\Http\Controllers\AdministracionNominaController::daysPassed($calculationsNetIncome['fechaIngreso'],$calculationsNetIncome['fechaBaja']);

				$calculationsNetIncome['añosCompletos']	= floor($calculationsNetIncome['diasTrabajadosParaAñosCompletos']/365);
				if ($calculationsNetIncome['añosTrabajados'] > 24) 
				{
					$calculationsNetIncome['diasDeVacaciones']	= 20;
				}
				else
				{
					$calculationsNetIncome['diasDeVacaciones']	= App\ParameterVacation::where('fromYear','<=',$calculationsNetIncome['añosTrabajados'])->where('toYear','>=',$calculationsNetIncome['añosTrabajados'])->first()->days;
				}

				//------------------------------------------------------------------
				
				$calculationsNetIncome['prima_vac_esp']		= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
				switch ($t_nominaemployee->idCatPeriodicity) 
				{
					case '02':
						$calculationsNetIncome['divisor'] = 7;
						break;

					case '04':
						$calculationsNetIncome['divisor'] = 15;
						break;

					case '05':
						$d = new DateTime(Carbon::now());
						$calculationsNetIncome['divisor'] = App\Http\Controllers\AdministracionNominaController::days_month($d->format('m'),$d->format('Y'));
						break;
					
					default:
						break;
				}

				$calculationsNetIncome['calc_sdi']	= $t_nominaemployee->total/$calculationsNetIncome['divisor'];
				$calculationsNetIncome['sdi']		= $calculationsNetIncome['calc_sdi'];
				$calculationsNetIncome['sd']		= round($calculationsNetIncome['sdi']/((($calculationsNetIncome['diasDeVacaciones']*$calculationsNetIncome['prima_vac_esp'])+15+365)/365),2);
				
				$calculationsNetIncome['diasTrabajadosM']	= $t_nominaemployee->worked_days;
				
				$calculationsNetIncome['diasParaVacaciones']	= ($calculationsNetIncome['diasDeVacaciones']*$calculationsNetIncome['diasTrabajadosM'])/365;
				//dias trabajados para aguinaldo va del 1 de enero a la fecha de baja
				$date1 = new \DateTime(date("Y").'-01-01');
				$date2 = new \DateTime($calculationsNetIncome['fechaIngreso']);
				if ($date2 > $date1) 
				{
					$fechaParaDiasAguinaldo = $calculationsNetIncome['fechaIngreso'];
				}
				else
				{
					$fechaParaDiasAguinaldo = date("Y").'-01-01';
				}
				$calculationsNetIncome['diasTrabajadosParaAguinaldo'] = App\Http\Controllers\AdministracionNominaController::daysPassed($fechaParaDiasAguinaldo,$calculationsNetIncome['fechaBaja'])+1;

				$calculationsNetIncome['diasParaAguinaldo'] 	= ($calculationsNetIncome['diasTrabajadosParaAguinaldo']*15)/365;

				if ($typePayroll == '004') 
				{
					$calculationsNetIncome['sueldoPorLiquidacion']		= round($calculationsNetIncome['sd']*90,6);
					$calculationsNetIncome['veinteDiasPorAñoServicio']	= round(20*$calculationsNetIncome['añosCompletos']*$calculationsNetIncome['sd'],6);
					
					// VARIABLES -------------------------------------------------------
					$calculationsNetIncome['uma']							= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
					$calculationsNetIncome['salarioMinimo']					= App\Parameter::where('parameter_name','SALARY_VDF')->first()->parameter_value; 
					$calculationsNetIncome['valorPrimaAntiguedad']			= $calculationsNetIncome['salarioMinimo']*2;
					$calculationsNetIncome['exento']							= $calculationsNetIncome['uma']*90; 
					$calculationsNetIncome['valorAguinaldoExento']			= $calculationsNetIncome['uma']*30; 
					$calculationsNetIncome['valorPrimaVacacaionalExenta']	= $calculationsNetIncome['uma']*15; 
					$calculationsNetIncome['valorIndemnizacionExenta']		= $calculationsNetIncome['uma']*90;
					// ------------------------------------------------------------------

					if ($calculationsNetIncome['sd']>=$calculationsNetIncome['valorPrimaAntiguedad']) 
					{
						$calculationsNetIncome['primaAntiguedad'] = round($calculationsNetIncome['añosCompletos']*12*$calculationsNetIncome['valorPrimaAntiguedad'],6);
					}
					else
					{
						$calculationsNetIncome['primaAntiguedad'] = round($calculationsNetIncome['añosCompletos']*12*$calculationsNetIncome['sd'],6);
					}

					// ------------------------------------------------------------------

					$calculationsNetIncome['indemnizacion'] =  round($calculationsNetIncome['sueldoPorLiquidacion']+$calculationsNetIncome['veinteDiasPorAñoServicio']+$calculationsNetIncome['primaAntiguedad'],6);

					if ($calculationsNetIncome['indemnizacion'] < $calculationsNetIncome['valorIndemnizacionExenta']) 
					{
						$calculationsNetIncome['indemnizacionExcenta']	= $calculationsNetIncome['indemnizacion'];
					}
					else
					{
						$calculationsNetIncome['indemnizacionExcenta']	= $calculationsNetIncome['valorIndemnizacionExenta'];
					}


					if ($calculationsNetIncome['indemnizacion'] > $calculationsNetIncome['valorIndemnizacionExenta']) 
					{
						$calculationsNetIncome['indemnizacionGravada']	= $calculationsNetIncome['indemnizacion']-$calculationsNetIncome['indemnizacionExcenta'];
					}
					else
					{
						$calculationsNetIncome['indemnizacionGravada']	= 0;
					}

					$calculationsNetIncome['vacaciones']				= $calculationsNetIncome['diasParaVacaciones']*$calculationsNetIncome['sd'];


					// PERCEPCIONES AGUINALDO---------------------------------------------------------------------

					if (($calculationsNetIncome['diasParaAguinaldo'] * $calculationsNetIncome['sd']) < $calculationsNetIncome['valorAguinaldoExento']) 
					{
						$calculationsNetIncome['aguinaldoExento'] = round($calculationsNetIncome['diasParaAguinaldo'] * $calculationsNetIncome['sd'],2);
					}
					else
					{
						$calculationsNetIncome['aguinaldoExento'] = $calculationsNetIncome['valorAguinaldoExento'];
					}

					if (($calculationsNetIncome['diasParaAguinaldo'] * $calculationsNetIncome['sd']) > $calculationsNetIncome['valorAguinaldoExento']) 
					{
						$calculationsNetIncome['aguinaldoGravable'] = ($calculationsNetIncome['diasParaAguinaldo'] * $calculationsNetIncome['sd'])-$calculationsNetIncome['aguinaldoExento'];
					}
					else
					{
						$calculationsNetIncome['aguinaldoGravable'] = 0;
					}


					//-------- PERCEPCIONES ---------------------------------------------------------------


					if (($calculationsNetIncome['vacaciones']*$calculationsNetIncome['prima_vac_esp'])<$calculationsNetIncome['valorPrimaVacacaionalExenta'])
					{
						$calculationsNetIncome['primaVacacionalExenta'] = round($calculationsNetIncome['vacaciones']*$calculationsNetIncome['prima_vac_esp'],6);
					}
					else
					{
						$calculationsNetIncome['primaVacacionalExenta'] = $calculationsNetIncome['valorPrimaVacacaionalExenta'];
					}

					if (($calculationsNetIncome['vacaciones']*$calculationsNetIncome['prima_vac_esp'])>$calculationsNetIncome['valorPrimaVacacaionalExenta'])
					{
						$calculationsNetIncome['primaVacacionalGravada'] = round(($calculationsNetIncome['vacaciones']*$calculationsNetIncome['prima_vac_esp'])-$calculationsNetIncome['primaVacacionalExenta'],6);
					}
					else
					{
						$calculationsNetIncome['primaVacacionalGravada'] = 0;
					}


					$calculationsNetIncome['otrasPercepciones'] = $t_nominaemployee->other_perception;
					$calculationsNetIncome['totalPercepciones'] = round(round($calculationsNetIncome['sueldoPorLiquidacion'],2)+round($calculationsNetIncome['veinteDiasPorAñoServicio'],2)+$calculationsNetIncome['primaAntiguedad']+round($calculationsNetIncome['vacaciones'],2)+round($calculationsNetIncome['aguinaldoExento'],2)+round($calculationsNetIncome['aguinaldoGravable'],2)+round($calculationsNetIncome['primaVacacionalExenta'],2)+round($calculationsNetIncome['primaVacacionalGravada'],2)+round($calculationsNetIncome['otrasPercepciones'],2),2);

					// ------------------------------------------------------------------------------------
				}
				else
				{
					// VARIABLES -------------------------------------------------------
					$calculationsNetIncome['uma']							= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
					$calculationsNetIncome['salarioMinimo']					= App\Parameter::where('parameter_name','SALARY_VDF')->first()->parameter_value; 
					$calculationsNetIncome['valorPrimaAntiguedad']			= $calculationsNetIncome['salarioMinimo']*2;
					$calculationsNetIncome['exento']							= $calculationsNetIncome['uma']*90; 
					$calculationsNetIncome['valorAguinaldoExento']			= $calculationsNetIncome['uma']*30; 
					$calculationsNetIncome['valorPrimaVacacaionalExenta']	= $calculationsNetIncome['uma']*15; 
					$calculationsNetIncome['valorIndemnizacionExenta']		= $calculationsNetIncome['uma']*90;
					// ------------------------------------------------------------------

					if ($calculationsNetIncome['sd']>$calculationsNetIncome['valorPrimaAntiguedad']) 
					{
						$calculationsNetIncome['primaAntiguedad'] = round($calculationsNetIncome['añosCompletos']*12*$calculationsNetIncome['valorPrimaAntiguedad'],6);
					}
					else
					{
						$calculationsNetIncome['primaAntiguedad'] = round($calculationsNetIncome['añosCompletos']*12*$calculationsNetIncome['sd'],6);
					}

					// ------------------------------------------------------------------

					if ($calculationsNetIncome['primaAntiguedad'] < $calculationsNetIncome['valorIndemnizacionExenta']) 
					{
						$calculationsNetIncome['indemnizacionExcenta']	= $calculationsNetIncome['primaAntiguedad'];
					}
					else
					{
						$calculationsNetIncome['indemnizacionExcenta']	= $calculationsNetIncome['valorIndemnizacionExenta'];
					}


					if ($calculationsNetIncome['primaAntiguedad'] > $calculationsNetIncome['valorIndemnizacionExenta']) 
					{
						$calculationsNetIncome['indemnizacionGravada']	= $calculationsNetIncome['primaAntiguedad']-$calculationsNetIncome['indemnizacionExcenta'];
					}
					else
					{
						$calculationsNetIncome['indemnizacionGravada']	= 0;
					}

					// ------------------------------------------------------------------
					
					$calculationsNetIncome['vacaciones']				= $calculationsNetIncome['diasParaVacaciones']*$calculationsNetIncome['sd'];


					// PERCEPCIONES AGUINALDO---------------------------------------------------------------------

					if (($calculationsNetIncome['diasParaAguinaldo'] * $calculationsNetIncome['sd']) < $calculationsNetIncome['valorAguinaldoExento']) 
					{
						$calculationsNetIncome['aguinaldoExento'] = round($calculationsNetIncome['diasParaAguinaldo'] * $calculationsNetIncome['sd'],2);
					}
					else
					{
						$calculationsNetIncome['aguinaldoExento'] = round($calculationsNetIncome['valorAguinaldoExento'],2);
					}

					if (($calculationsNetIncome['diasParaAguinaldo'] * $calculationsNetIncome['sd']) > $calculationsNetIncome['valorAguinaldoExento']) 
					{
						$calculationsNetIncome['aguinaldoGravable'] = round(($calculationsNetIncome['diasParaAguinaldo'] * $calculationsNetIncome['sd'])-$calculationsNetIncome['aguinaldoExento'],6);
					}
					else
					{
						$calculationsNetIncome['aguinaldoGravable'] = 0;
					}


					//-------- PERCEPCIONES ---------------------------------------------------------------


					if (($calculationsNetIncome['vacaciones']*$calculationsNetIncome['prima_vac_esp'])<$calculationsNetIncome['valorPrimaVacacaionalExenta'])
					{
						$calculationsNetIncome['primaVacacionalExenta'] = round($calculationsNetIncome['vacaciones']*$calculationsNetIncome['prima_vac_esp'],6);
					}
					else
					{
						$calculationsNetIncome['primaVacacionalExenta'] = round($calculationsNetIncome['valorPrimaVacacaionalExenta'],6);
					}

					if (($calculationsNetIncome['vacaciones']*$calculationsNetIncome['prima_vac_esp'])>$calculationsNetIncome['valorPrimaVacacaionalExenta'])
					{
						$calculationsNetIncome['primaVacacionalGravada'] = round(($calculationsNetIncome['vacaciones']*$calculationsNetIncome['prima_vac_esp'])-$calculationsNetIncome['primaVacacionalExenta'],6);
					}
					else
					{
						$calculationsNetIncome['primaVacacionalGravada'] = 0;
					}

					$calculationsNetIncome['otrasPercepciones']	= $t_nominaemployee->other_perception;
					$calculationsNetIncome['totalPercepciones']	= round(round($calculationsNetIncome['primaAntiguedad'],2)+round($calculationsNetIncome['vacaciones'],2)+round($calculationsNetIncome['aguinaldoExento'],2)+round($calculationsNetIncome['aguinaldoGravable'],2)+round($calculationsNetIncome['primaVacacionalExenta'],2)+round($calculationsNetIncome['primaVacacionalGravada'],2)+round($calculationsNetIncome['otrasPercepciones'],2),2);
				}

				$calculationsNetIncome['netIncome'] = $calculationsNetIncome['totalPercepciones'];
				// ----- calculo para dias de vacaciones ---------------------------
				$calculations					= [];

				$calculations['fechaIngreso']	= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
				$calculations['fechaBaja']		= $t_nominaemployee->down_date;
				
				$calculations['fechaActual']	= Carbon::now();
				$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
				$calculations['añosTrabajados']	= ceil($calculations['diasTrabajados']/365);

				$calculations['diasTrabajadosParaAñosCompletos'] = App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaBaja']);

				$calculations['añosCompletos']	= floor($calculations['diasTrabajadosParaAñosCompletos']/365);
				if ($calculations['añosTrabajados'] > 24) 
				{
					$calculations['diasDeVacaciones']	= 20;
				}
				else
				{
					$calculations['diasDeVacaciones']	= App\ParameterVacation::where('fromYear','<=',$calculations['añosTrabajados'])->where('toYear','>=',$calculations['añosTrabajados'])->first()->days;
				}

				//------------------------------------------------------------------
				
				$calculations['prima_vac_esp']		= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
				$calculations['sdi']				= $t_nominaemployee->workerData->first()->sdi;
				$calculations['sd']					= round($calculations['sdi']/((($calculations['diasDeVacaciones']*$calculations['prima_vac_esp'])+15+365)/365),2);
				
				$calculations['diasTrabajadosM']	= $t_nominaemployee->worked_days;
				
				$calculations['diasParaVacaciones']	= ($calculations['diasDeVacaciones']*$calculations['diasTrabajadosM'])/365;
				//dias trabajados para aguinaldo va del 1 de enero a la fecha de baja
				$date1 = new \DateTime(date("Y").'-01-01');
				$date2 = new \DateTime($calculations['fechaIngreso']);
				if ($date2 > $date1) 
				{
					$fechaParaDiasAguinaldo = $calculations['fechaIngreso'];
				}
				else
				{
					$fechaParaDiasAguinaldo = date("Y").'-01-01';
				}
				$calculations['diasTrabajadosParaAguinaldo'] = App\Http\Controllers\AdministracionNominaController::daysPassed($fechaParaDiasAguinaldo,$calculations['fechaBaja'])+1;

				$calculations['diasParaAguinaldo'] 	= ($calculations['diasTrabajadosParaAguinaldo']*15)/365;

				if ($typePayroll == '004') 
				{
					$calculations['sueldoPorLiquidacion']		= round($calculations['sd']*90,6);
					$calculations['veinteDiasPorAñoServicio']	= round(20*$calculations['añosCompletos']*$calculations['sd'],6);
					
					// VARIABLES -------------------------------------------------------
					$calculations['uma']							= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
					$calculations['salarioMinimo']					= App\Parameter::where('parameter_name','SALARY_VDF')->first()->parameter_value; 
					$calculations['valorPrimaAntiguedad']			= $calculations['salarioMinimo']*2;
					$calculations['exento']							= $calculations['uma']*90; 
					$calculations['valorAguinaldoExento']			= $calculations['uma']*30; 
					$calculations['valorPrimaVacacaionalExenta']	= $calculations['uma']*15; 
					$calculations['valorIndemnizacionExenta']		= $calculations['uma']*90;
					// ------------------------------------------------------------------

					if ($calculations['sd']>=$calculations['valorPrimaAntiguedad']) 
					{
						$calculations['primaAntiguedad'] = round($calculations['añosCompletos']*12*$calculations['valorPrimaAntiguedad'],6);
					}
					else
					{
						$calculations['primaAntiguedad'] = round($calculations['añosCompletos']*12*$calculations['sd'],6);
					}

					// ------------------------------------------------------------------

					$calculations['indemnizacion'] =  round($calculations['sueldoPorLiquidacion']+$calculations['veinteDiasPorAñoServicio']+$calculations['primaAntiguedad'],6);

					if ($calculations['indemnizacion'] < $calculations['valorIndemnizacionExenta']) 
					{
						$calculations['indemnizacionExcenta']	= $calculations['indemnizacion'];
					}
					else
					{
						$calculations['indemnizacionExcenta']	= $calculations['valorIndemnizacionExenta'];
					}


					if ($calculations['indemnizacion'] > $calculations['valorIndemnizacionExenta']) 
					{
						$calculations['indemnizacionGravada']	= $calculations['indemnizacion']-$calculations['indemnizacionExcenta'];
					}
					else
					{
						$calculations['indemnizacionGravada']	= 0;
					}

					$calculations['vacaciones']				= $calculations['diasParaVacaciones']*$calculations['sd'];


					// PERCEPCIONES AGUINALDO---------------------------------------------------------------------

					if (($calculations['diasParaAguinaldo'] * $calculations['sd']) < $calculations['valorAguinaldoExento']) 
					{
						$calculations['aguinaldoExento'] = round($calculations['diasParaAguinaldo'] * $calculations['sd'],2);
					}
					else
					{
						$calculations['aguinaldoExento'] = $calculations['valorAguinaldoExento'];
					}

					if (($calculations['diasParaAguinaldo'] * $calculations['sd']) > $calculations['valorAguinaldoExento']) 
					{
						$calculations['aguinaldoGravable'] = ($calculations['diasParaAguinaldo'] * $calculations['sd'])-$calculations['aguinaldoExento'];
					}
					else
					{
						$calculations['aguinaldoGravable'] = 0;
					}


					//-------- PERCEPCIONES ---------------------------------------------------------------


					if (($calculations['vacaciones']*$calculations['prima_vac_esp'])<$calculations['valorPrimaVacacaionalExenta'])
					{
						$calculations['primaVacacionalExenta'] = round($calculations['vacaciones']*$calculations['prima_vac_esp'],6);
					}
					else
					{
						$calculations['primaVacacionalExenta'] = $calculations['valorPrimaVacacaionalExenta'];
					}

					if (($calculations['vacaciones']*$calculations['prima_vac_esp'])>$calculations['valorPrimaVacacaionalExenta'])
					{
						$calculations['primaVacacionalGravada'] = round(($calculations['vacaciones']*$calculations['prima_vac_esp'])-$calculations['primaVacacionalExenta'],6);
					}
					else
					{
						$calculations['primaVacacionalGravada'] = 0;
					}


					$calculations['otrasPercepciones'] = $t_nominaemployee->other_perception;
					$calculations['totalPercepciones'] = round(round($calculations['sueldoPorLiquidacion'],2)+round($calculations['veinteDiasPorAñoServicio'],2)+$calculations['primaAntiguedad']+round($calculations['vacaciones'],2)+round($calculations['aguinaldoExento'],2)+round($calculations['aguinaldoGravable'],2)+round($calculations['primaVacacionalExenta'],2)+round($calculations['primaVacacionalGravada'],2)+round($calculations['otrasPercepciones'],2),2);

					// ------------------------------------------------------------------------------------
				}
				else
				{
					// VARIABLES -------------------------------------------------------
					$calculations['uma']							= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
					$calculations['salarioMinimo']					= App\Parameter::where('parameter_name','SALARY_VDF')->first()->parameter_value; 
					$calculations['valorPrimaAntiguedad']			= $calculations['salarioMinimo']*2;
					$calculations['exento']							= $calculations['uma']*90; 
					$calculations['valorAguinaldoExento']			= $calculations['uma']*30; 
					$calculations['valorPrimaVacacaionalExenta']	= $calculations['uma']*15; 
					$calculations['valorIndemnizacionExenta']		= $calculations['uma']*90;
					// ------------------------------------------------------------------

					if ($calculations['sd']>$calculations['valorPrimaAntiguedad']) 
					{
						$calculations['primaAntiguedad'] = round($calculations['añosCompletos']*12*$calculations['valorPrimaAntiguedad'],6);
					}
					else
					{
						$calculations['primaAntiguedad'] = round($calculations['añosCompletos']*12*$calculations['sd'],6);
					}

					// ------------------------------------------------------------------

					if ($calculations['primaAntiguedad'] < $calculations['valorIndemnizacionExenta']) 
					{
						$calculations['indemnizacionExcenta']	= $calculations['primaAntiguedad'];
					}
					else
					{
						$calculations['indemnizacionExcenta']	= $calculations['valorIndemnizacionExenta'];
					}


					if ($calculations['primaAntiguedad'] > $calculations['valorIndemnizacionExenta']) 
					{
						$calculations['indemnizacionGravada']	= $calculations['primaAntiguedad']-$calculations['indemnizacionExcenta'];
					}
					else
					{
						$calculations['indemnizacionGravada']	= 0;
					}

					// ------------------------------------------------------------------
					
					$calculations['vacaciones']				= $calculations['diasParaVacaciones']*$calculations['sd'];


					// PERCEPCIONES AGUINALDO---------------------------------------------------------------------

					if (($calculations['diasParaAguinaldo'] * $calculations['sd']) < $calculations['valorAguinaldoExento']) 
					{
						$calculations['aguinaldoExento'] = round($calculations['diasParaAguinaldo'] * $calculations['sd'],2);
					}
					else
					{
						$calculations['aguinaldoExento'] = round($calculations['valorAguinaldoExento'],2);
					}

					if (($calculations['diasParaAguinaldo'] * $calculations['sd']) > $calculations['valorAguinaldoExento']) 
					{
						$calculations['aguinaldoGravable'] = round(($calculations['diasParaAguinaldo'] * $calculations['sd'])-$calculations['aguinaldoExento'],6);
					}
					else
					{
						$calculations['aguinaldoGravable'] = 0;
					}


					//-------- PERCEPCIONES ---------------------------------------------------------------


					if (($calculations['vacaciones']*$calculations['prima_vac_esp'])<$calculations['valorPrimaVacacaionalExenta'])
					{
						$calculations['primaVacacionalExenta'] = round($calculations['vacaciones']*$calculations['prima_vac_esp'],6);
					}
					else
					{
						$calculations['primaVacacionalExenta'] = round($calculations['valorPrimaVacacaionalExenta'],6);
					}

					if (($calculations['vacaciones']*$calculations['prima_vac_esp'])>$calculations['valorPrimaVacacaionalExenta'])
					{
						$calculations['primaVacacionalGravada'] = round(($calculations['vacaciones']*$calculations['prima_vac_esp'])-$calculations['primaVacacionalExenta'],6);
					}
					else
					{
						$calculations['primaVacacionalGravada'] = 0;
					}

					$calculations['otrasPercepciones']	= $t_nominaemployee->other_perception;
					$calculations['totalPercepciones']	= round(round($calculations['primaAntiguedad'],2)+round($calculations['vacaciones'],2)+round($calculations['aguinaldoExento'],2)+round($calculations['aguinaldoGravable'],2)+round($calculations['primaVacacionalExenta'],2)+round($calculations['primaVacacionalGravada'],2)+round($calculations['otrasPercepciones'],2),2);
				}
				

				//-------- RETENCIONES ----------------------------------------------------------------

				// ISR 1ER FRACCION

				$calculations['baseISR_fraccion1']			= round(((($calculations['vacaciones']+$calculations['aguinaldoGravable']+$calculations['primaVacacionalGravada'])/365)*30.4)+($calculations['sd']*30),6);
				$parameterISRF1								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion1'])->where('lapse',30)->get();

				$calculations['limiteInferior_fraccion1']	= $parameterISRF1->last()->inferior;
				$calculations['excedente_fraccion1']		= round($calculations['baseISR_fraccion1']-$calculations['limiteInferior_fraccion1'],6);
				$calculations['factor_fraccion1']			= round($parameterISRF1->last()->excess/100,6);
				$calculations['isrMarginal_fraccion1']		= round($calculations['excedente_fraccion1'] * $calculations['factor_fraccion1'],6);
				$calculations['cuotaFija_fraccion1']		= round($parameterISRF1->last()->quota,6);
				$calculations['isr_fraccion1']				= round($calculations['isrMarginal_fraccion1']+$calculations['cuotaFija_fraccion1'],6);

				// ISR 2DA FRACCION

				$calculations['baseISR_fraccion2']			= round($calculations['sd']*30,6);
				$parameterISRF2								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion2'])->where('lapse',30)->get();

				$calculations['limiteInferior_fraccion2']	= $parameterISRF2->last()->inferior;
				$calculations['excedente_fraccion2']		= round($calculations['baseISR_fraccion2']-$calculations['limiteInferior_fraccion2'],6);
				$calculations['factor_fraccion2']			= round($parameterISRF2->last()->excess/100,6);
				$calculations['isrMarginal_fraccion2']		= round($calculations['excedente_fraccion2'] * $calculations['factor_fraccion2'],6);
				$calculations['cuotaFija_fraccion2']		= round($parameterISRF2->last()->quota,6);
				$calculations['isr_fraccion2']				= round($calculations['isrMarginal_fraccion2']+$calculations['cuotaFija_fraccion2'],6);

				$calculations['resta']		= round($calculations['isr_fraccion1']-$calculations['isr_fraccion2'],6);
				if ($calculations['resta'] == 0) 
				{
					$calculations['factor1']	= 0;
					$calculations['factor2']	= 0;
					$calculations['isr']		= 0;
				}
				else
				{
					$calculations['factor1']	= round(((($calculations['vacaciones']+$calculations['aguinaldoGravable']+$calculations['primaVacacionalGravada'])/365)*30.4),6);
					$calculations['factor2']	= round($calculations['resta']/$calculations['factor1'],6);
					$calculations['isr']		= round($calculations['factor2']*($calculations['vacaciones']+$calculations['aguinaldoGravable']+$calculations['primaVacacionalGravada']),6);
				}

				// ISR FINIQUITO (INDEMNIZACION)

				$calculations['baseTotalDePercepciones']	= round($calculations['sd']*30,6);
				$calculations['baseISR_finiquito']			= $calculations['baseTotalDePercepciones'];
				
				$parameterISRFiniquito						= App\ParameterISR::where('inferior','<=',$calculations['baseISR_finiquito'])->where('lapse',30)->get();
				
				$calculations['limiteInferior_finiquito']	= $parameterISRFiniquito->last()->inferior;
				$calculations['excedente_finiquito']		= round($calculations['baseISR_finiquito']-$calculations['limiteInferior_finiquito'],6);
				$calculations['factor_finiquito']			= round($parameterISRFiniquito->last()->excess/100,6);
				$calculations['isrMarginal_finiquito']		= round($calculations['excedente_finiquito'] * $calculations['factor_finiquito'],6);
				$calculations['cuotaFija_finiquito']		= round($parameterISRFiniquito->last()->quota,6);
				$calculations['isr_salario']				= round($calculations['isrMarginal_finiquito']+$calculations['cuotaFija_finiquito'],6);
				
				$calculations['isr_finiquito']				= round(($calculations['isr_salario']/$calculations['baseTotalDePercepciones'])*$calculations['indemnizacionGravada'],6);
				
				$calculations['totalISR']					= $calculations['isr_finiquito'] + $calculations['isr']; 
				$calculations['otrasRetenciones']			= $t_nominaemployee->other_retention;
				if ($t_nominaemployee->workerData->first()->alimonyDiscountType != '') 
				{
					$calculations['totalRetencionesTemp']  	= round($calculations['totalISR'],2)+round($calculations['otrasRetenciones'],2);
					$calculations['netIncomeTemp']			= round($calculations['totalPercepciones']-$calculations['totalRetencionesTemp'],2);

					switch ($t_nominaemployee->workerData->first()->alimonyDiscountType) 
					{
						case 1: //monto
							$calculations['amountAlimony']	= $t_nominaemployee->workerData->first()->alimonyDiscount;
							$calculations['alimony']		= $calculations['amountAlimony'];
							break;

						case 2: // porcentaje
							$calculations['amountAlimony']	= $t_nominaemployee->workerData->first()->alimonyDiscount;
							$calculations['alimony']		= ($calculations['netIncomeTemp']*$calculations['amountAlimony'])/100;
							break;
						default:
							# code...
							break;
					}

					$calculations['totalRetenciones']	= round(round($calculations['totalISR'],2)+round($calculations['alimony'],2),2)+round($calculations['otrasRetenciones'],2);
					$calculations['netIncome']			= round($calculations['totalPercepciones']-$calculations['totalRetenciones'],2);
				}
				else
				{
					$calculations['alimony']          = 0;
					$calculations['totalRetenciones'] = round($calculations['totalISR'],2)+round($calculations['otrasRetenciones'],2);
					$calculations['netIncome']        = round($calculations['totalPercepciones']-$calculations['totalRetenciones'],2);
				}

				// --------------------------------------------------------------------------------------------

				
				$t_liquidation								= App\Liquidation::find($t_nominaemployee->liquidation->first()->idLiquidation);
				$t_liquidation->idnominaEmployee			= $t_nominaemployee->idnominaEmployee;
				$t_liquidation->sd							= $calculations['sd'];
				$t_liquidation->sdi							= $calculations['sdi'];
				$t_liquidation->admissionDate				= $calculations['fechaIngreso'];
				$t_liquidation->downDate					= $calculations['fechaBaja'];
				$t_liquidation->fullYears					= $calculations['añosCompletos'];
				$t_liquidation->workedDays					= $calculations['diasTrabajadosM'];
				$t_liquidation->holidayDays					= $calculations['diasParaVacaciones'];
				$t_liquidation->bonusDays					= $calculations['diasParaAguinaldo'];
				if ($typePayroll == '004') 
				{
					$t_liquidation->liquidationSalary			= $calculations['sueldoPorLiquidacion'];
					$t_liquidation->twentyDaysPerYearOfServices	= $calculations['veinteDiasPorAñoServicio'];
				}
				$t_liquidation->seniorityPremium			= $calculations['primaAntiguedad'];
				$t_liquidation->exemptCompensation			= $calculations['indemnizacionExcenta'];
				$t_liquidation->taxedCompensation			= $calculations['indemnizacionGravada'];
				$t_liquidation->holidays					= $calculations['vacaciones'];
				$t_liquidation->exemptBonus					= $calculations['aguinaldoExento'];
				$t_liquidation->taxableBonus				= $calculations['aguinaldoGravable'];
				$t_liquidation->holidayPremiumExempt		= $calculations['primaVacacionalExenta'];
				$t_liquidation->holidayPremiumTaxed			= $calculations['primaVacacionalGravada'];
				$t_liquidation->otherPerception				= $calculations['otrasPercepciones'];
				$t_liquidation->totalPerceptions			= $calculations['totalPercepciones'];
				$t_liquidation->isr							= $calculations['totalISR'];
				$t_liquidation->other_retention				= $calculations['otrasRetenciones'];
				$t_liquidation->alimony 					= $calculations['alimony'];
				$t_liquidation->totalRetentions				= $calculations['totalRetenciones'];
				$t_liquidation->netIncome					= $calculations['netIncome'];
				$t_liquidation->totalIncomeLiquidation 		= $calculationsNetIncome['netIncome'];
				$t_liquidation->save();

				$new_total				= round($t_liquidation->totalIncomeLiquidation - $t_liquidation->netIncome,2);
				$totalIncomeLiquidation	= $t_liquidation->totalIncomeLiquidation;
				$calculations			= [];
				$calculationsNetIncome	= [];
				$totalRequest			= 0;
				$t_nomina				= App\Nomina::find($t_nominaemployee->idnomina);
				foreach ($t_nomina->nominaEmployee as $n) 
				{
					$totalRequest += $n->liquidation->first()->netIncome + $n->liquidation->first()->alimony;
				}
				$t_nomina->amount			= $totalRequest;
				$t_nomina->save();

				$req	= App\RequestModel::find($t_nomina->idFolio);
				$rfn	= App\RequestModel::where('kind',16)
						->where('idprenomina',$req->idprenomina)
						->where('idDepartment',$req->idDepartment)
						->where('taxPayment',0)
						->first();

				if ($rfn != '') 
				{
					$nominaemp = App\NominaEmployee::where('idrealEmployee',$t_nominaemployee->idrealEmployee)
							->where('idnomina',$rfn->nominasReal->first()->idnomina)
							->first();
					if ($nominaemp != '' || $nominaemp != null) 
					{
						$t_nominaemployee_nf = App\NominaEmployeeNF::where('idnominaEmployee',$nominaemp->idnominaEmployee)->first();
						if ($t_nominaemployee_nf != '' || $t_nominaemployee_nf != null) 
						{
							App\DiscountsNomina::where('idnominaemployeenf',$t_nominaemployee_nf->idnominaemployeenf)->delete();
							App\ExtrasNomina::where('idnominaemployeenf',$t_nominaemployee_nf->idnominaemployeenf)->delete();

							$t_nominaemployee_nf->netIncome			= $totalIncomeLiquidation;
							$t_nominaemployee_nf->complementPartial	= $new_total;
							$t_nominaemployee_nf->amount			= $new_total;
							$t_nominaemployee_nf->save();
						}
					}
				}
				return true;
				break;

			case '005':

					$t_nominaemployee					= App\NominaEmployee::find($idnominaemployee);

					$calculationsNetIncome = [];

					// ----- calculo para dias de vacaciones ---------------------------
					$calculationsNetIncome['fechaIngreso']		= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
					$calculationsNetIncome['fechaActual']		= Carbon::now();
					$calculationsNetIncome['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculationsNetIncome['fechaIngreso'],$calculationsNetIncome['fechaActual']);
					$calculationsNetIncome['yearsWork']			= ceil($calculationsNetIncome['diasTrabajados']/365);
					if ($calculationsNetIncome['yearsWork'] > 24) 
					{
						$calculationsNetIncome['vacationDays']	= 20;
					}
					else
					{
						$calculationsNetIncome['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculationsNetIncome['yearsWork'])->where('toYear','>=',$calculationsNetIncome['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculationsNetIncome['yearsWork'])->where('toYear','>=',$calculationsNetIncome['yearsWork'])->first()->days : 0;
					}

					//------------------------------------------------------------------
					
					$calculationsNetIncome['prima_vac_esp']		= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;

					switch ($t_nominaemployee->idCatPeriodicity) 
					{
						case '02':
							$calculationsNetIncome['divisor'] = 7;
							break;

						case '04':
							$calculationsNetIncome['divisor'] = 15;
							break;

						case '05':
							$d = new DateTime(Carbon::now());
							$calculationsNetIncome['divisor'] = App\Http\Controllers\AdministracionNominaController::days_month($d->format('m'),$d->format('Y'));
							break;
						
						default:
							break;
					}

					$calculationsNetIncome['calc_sdi']				= $t_nominaemployee->total/$calculationsNetIncome['divisor'];
					$calculationsNetIncome['sdi']					= $calculationsNetIncome['calc_sdi'];
					$calculationsNetIncome['sd']					= round($calculationsNetIncome['sdi']/((($calculationsNetIncome['vacationDays']*$calculationsNetIncome['prima_vac_esp'])+15+365)/365),2);
					
					$calculationsNetIncome['diasTrabajadosM']		= $t_nominaemployee->worked_days;
					
					$calculationsNetIncome['diasParaVacaciones']	= ($calculationsNetIncome['vacationDays']*$calculationsNetIncome['diasTrabajadosM'])/365;
					
					$calculationsNetIncome['uma']					= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
					$calculationsNetIncome['exento']				= $calculationsNetIncome['uma']*15; 

					//-------- PERCEPCIONES ---------------------------------------------------------------

					$calculationsNetIncome['vacaciones'] = $calculationsNetIncome['sd']*$calculationsNetIncome['diasParaVacaciones'];

					if (($calculationsNetIncome['sd']*$calculationsNetIncome['diasParaVacaciones']*$calculationsNetIncome['prima_vac_esp'])<$calculationsNetIncome['exento'])
					{
						$calculationsNetIncome['primaVacacionalExenta'] = round($calculationsNetIncome['sd']*$calculationsNetIncome['diasParaVacaciones']*$calculationsNetIncome['prima_vac_esp'],6);
					}
					else
					{
						$calculationsNetIncome['primaVacacionalExenta'] = $calculationsNetIncome['exento'];
					}

					if (($calculationsNetIncome['sd']*$calculationsNetIncome['diasParaVacaciones']*$calculationsNetIncome['prima_vac_esp'])>$calculationsNetIncome['exento'])
					{
						$calculationsNetIncome['primaVacacionalGravada'] = round(($calculationsNetIncome['sd']*$calculationsNetIncome['diasParaVacaciones']*$calculationsNetIncome['prima_vac_esp'])-$calculationsNetIncome['primaVacacionalExenta'],6);
					}
					else
					{
						$calculationsNetIncome['primaVacacionalGravada'] = 0;
					}

					$calculationsNetIncome['totalPercepciones'] = round(round($calculationsNetIncome['primaVacacionalExenta'],2)+round($calculationsNetIncome['primaVacacionalGravada'],2),2);
					$calculationsNetIncome['netIncome'] = $calculationsNetIncome['totalPercepciones'];

					$calculations = [];

					// ----- calculo para dias de vacaciones ---------------------------
					$calculations['fechaIngreso']	= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
					$calculations['fechaActual']	= Carbon::now();
					$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
					$calculations['yearsWork']		= ceil($calculations['diasTrabajados']/365);
					if ($calculations['yearsWork'] > 24) 
					{
						$calculations['vacationDays']	= 20;
					}
					else
					{
						$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
					}

					//------------------------------------------------------------------
					
					$calculations['prima_vac_esp']		= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
					$calculations['sdi']				= $t_nominaemployee->workerData->first()->sdi;
					$calculations['sd']					= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);
					
					$calculations['diasTrabajadosM']	= $t_nominaemployee->worked_days;
					
					$calculations['diasParaVacaciones']	= ($calculations['vacationDays']*$calculations['diasTrabajadosM'])/365;
					
					$calculations['uma']				= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
					$calculations['exento']				= $calculations['uma']*15; 

					//-------- PERCEPCIONES ---------------------------------------------------------------

					$calculations['vacaciones'] = $calculations['sd']*$calculations['diasParaVacaciones'];

					if (($calculations['sd']*$calculations['diasParaVacaciones']*$calculations['prima_vac_esp'])<$calculations['exento'])
					{
						$calculations['primaVacacionalExenta'] = round($calculations['sd']*$calculations['diasParaVacaciones']*$calculations['prima_vac_esp'],6);
					}
					else
					{
						$calculations['primaVacacionalExenta'] = $calculations['exento'];
					}

					if (($calculations['sd']*$calculations['diasParaVacaciones']*$calculations['prima_vac_esp'])>$calculations['exento'])
					{
						$calculations['primaVacacionalGravada'] = round(($calculations['sd']*$calculations['diasParaVacaciones']*$calculations['prima_vac_esp'])-$calculations['primaVacacionalExenta'],6);
					}
					else
					{
						$calculations['primaVacacionalGravada'] = 0;
					}

					$calculations['totalPercepciones'] = round(round($calculations['primaVacacionalExenta'],2)+round($calculations['primaVacacionalGravada'],2),2);

					// ------------------------------------------------------------------------------------

					//-------- RETENCIONES ----------------------------------------------------------------

					// ISR 1ER FRACCION

					$calculations['baseISR_fraccion1']			= round((($calculations['primaVacacionalGravada']/365)*30.4)+($calculations['sd']*30),6);
					$parameterISRF1								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion1'])->where('lapse',30)->get();

					$calculations['limiteInferior_fraccion1']	= $parameterISRF1->last()->inferior;
					$calculations['excedente_fraccion1']		= round($calculations['baseISR_fraccion1']-$calculations['limiteInferior_fraccion1'],6);
					$calculations['factor_fraccion1']			= round($parameterISRF1->last()->excess/100,6);
					$calculations['isrMarginal_fraccion1']		= round($calculations['excedente_fraccion1'] * $calculations['factor_fraccion1'],6);
					$calculations['cuotaFija_fraccion1']		= round($parameterISRF1->last()->quota,6);
					$calculations['isr_fraccion1']				= round($calculations['isrMarginal_fraccion1']+$calculations['cuotaFija_fraccion1'],6);

					// ISR 2DA FRACCION

					$calculations['baseISR_fraccion2']			= round($calculations['sd']*30,6);
					$parameterISRF2								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion2'])->where('lapse',30)->get();

					$calculations['limiteInferior_fraccion2']	= $parameterISRF2->last()->inferior;
					$calculations['excedente_fraccion2']		= round($calculations['baseISR_fraccion2']-$calculations['limiteInferior_fraccion2'],6);
					$calculations['factor_fraccion2']			= round($parameterISRF2->last()->excess/100,6);
					$calculations['isrMarginal_fraccion2']		= round($calculations['excedente_fraccion2'] * $calculations['factor_fraccion2'],6);
					$calculations['cuotaFija_fraccion2']		= round($parameterISRF2->last()->quota,6);
					$calculations['isr_fraccion2']				= round($calculations['isrMarginal_fraccion2']+$calculations['cuotaFija_fraccion2'],6);

					$calculations['resta']		= round($calculations['isr_fraccion1']-$calculations['isr_fraccion2'],6);
					if ($calculations['resta'] == 0) 
					{
						$calculations['factor1']	= 0;
						$calculations['factor2']	= 0;
						$calculations['isr']		= 0;
					}
					else
					{
						$calculations['factor1']	= round((($calculations['primaVacacionalGravada']/365) * 30.4),6);
						$calculations['factor2']	= round($calculations['resta']/$calculations['factor1'],6);
						$calculations['isr']		= round($calculations['factor2']*$calculations['primaVacacionalGravada'],6);
					}

					if ($t_nominaemployee->workerData->first()->alimonyDiscountType != '') 
					{
						$calculations['totalRetencionesTemp']  	= round($calculations['isr'],2);
					
						$calculations['netIncomeTemp']			= round($calculations['totalPercepciones']-$calculations['totalRetencionesTemp'],2);

						switch ($t_nominaemployee->workerData->first()->alimonyDiscountType) 
						{
							case 1: //monto
								$calculations['amountAlimony']	= $t_nominaemployee->workerData->first()->alimonyDiscount;
								$calculations['alimony']		= $calculations['amountAlimony'];
								break;

							case 2: // porcentaje
								$calculations['amountAlimony']	= $t_nominaemployee->workerData->first()->alimonyDiscount;
								$calculations['alimony']		= ($calculations['netIncomeTemp']*$calculations['amountAlimony'])/100;
								break;
							default:
								# code...
								break;
						}

						$calculations['totalRetenciones']	= round(round($calculations['isr'],2)+round($calculations['alimony'],2),2);
						$calculations['netIncome']			= round($calculations['totalPercepciones']-$calculations['totalRetenciones'],2);
					}
					else
					{ 
						$calculations['alimony']          = 0;
						$calculations['totalRetenciones'] = round($calculations['isr'],2);
						$calculations['netIncome']        = round($calculations['totalPercepciones']-$calculations['totalRetenciones'],2);
					}

					// --------------------------------------------------------------------------------------------

					$t_vacationpremium							= App\VacationPremium::find($t_nominaemployee->vacationPremium->first()->idvacationPremium);
					$t_vacationpremium->idnominaEmployee		= $t_nominaemployee->idnominaEmployee;
					$t_vacationpremium->sd						= $calculations['sd'];
					$t_vacationpremium->sdi						= $calculations['sdi'];
					$t_vacationpremium->dateOfAdmission			= $calculations['fechaIngreso'];
					$t_vacationpremium->workedDays				= $calculations['diasTrabajadosM'];
					$t_vacationpremium->holidaysDays			= $calculations['diasParaVacaciones'];
					$t_vacationpremium->holidays				= $calculations['vacaciones'];
					$t_vacationpremium->exemptHolidayPremium	= $calculations['primaVacacionalExenta'];
					$t_vacationpremium->holidayPremiumTaxed		= $calculations['primaVacacionalGravada'];
					$t_vacationpremium->totalPerceptions		= $calculations['totalPercepciones'];
					$t_vacationpremium->isr						= $calculations['isr'];
					$t_vacationpremium->alimony 				= $calculations['alimony'];
					$t_vacationpremium->totalTaxes				= $calculations['totalRetenciones'];
					$t_vacationpremium->netIncome				= $calculations['netIncome'];
					$t_vacationpremium->totalIncomeVP 			= $calculationsNetIncome['netIncome'];
					$t_vacationpremium->save();

					$new_total				= round($t_vacationpremium->totalIncomeVP - $t_vacationpremium->netIncome,2);
					$totalIncomeVP			= $t_vacationpremium->totalIncomeVP;
					$calculations			= [];
					$calculationsNetIncome	= [];
					$totalRequest			= 0;
					$t_nomina				= App\Nomina::find($t_nominaemployee->idnomina);
					foreach ($t_nomina->nominaEmployee as $n) 
					{
						$totalRequest += $n->vacationPremium->first()->netIncome + $n->vacationPremium->first()->alimony;
					}
					$t_nomina->amount			= $totalRequest;
					$t_nomina->save();

					$req	= App\RequestModel::find($t_nomina->idFolio);
					$rfn	= App\RequestModel::where('kind',16)
							->where('idprenomina',$req->idprenomina)
							->where('idDepartment',$req->idDepartment)
							->where('taxPayment',0)
							->first();

					if ($rfn != '') 
					{
						$nominaemp = App\NominaEmployee::where('idrealEmployee',$t_nominaemployee->idrealEmployee)
								->where('idnomina',$rfn->nominasReal->first()->idnomina)
								->first();
						if ($nominaemp != '' || $nominaemp != null) 
						{
							$t_nominaemployee_nf = App\NominaEmployeeNF::where('idnominaEmployee',$nominaemp->idnominaEmployee)->first();
							if ($t_nominaemployee_nf != '' || $t_nominaemployee_nf != null) 
							{
								App\DiscountsNomina::where('idnominaemployeenf',$t_nominaemployee_nf->idnominaemployeenf)->delete();
								App\ExtrasNomina::where('idnominaemployeenf',$t_nominaemployee_nf->idnominaemployeenf)->delete();

								$t_nominaemployee_nf->netIncome			= $totalIncomeVP;
								$t_nominaemployee_nf->complementPartial	= $new_total;
								$t_nominaemployee_nf->amount			= $new_total;
								$t_nominaemployee_nf->save();
							}
						}
					}
					return true;
				break;

			case '006':
					$idnomina = App\NominaEmployee::find($idnominaemployee)->idnomina;

					$nominas = App\NominaEmployee::where('idnomina',$idnomina)->get();


					$sumaDiasTrabajados	= 0;
					$sumaSueldoTotal	= 0;
					//------- calculo para sumatoria de dias trabajados y sueldo total ------------------------
					foreach ($nominas as $t_nominaemployee) 
					{						
						$sumaDiasTrabajados		 		+= $t_nominaemployee->worked_days;
						$calculations					= [];
						$calculations['fechaIngreso']	= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
						$calculations['fechaActual']	= Carbon::now();
						$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
						$calculations['yearsWork']		= ceil($calculations['diasTrabajados']/365);
						if ($calculations['yearsWork'] > 24) 
						{
							$calculations['vacationDays']	= 20;
						}
						else
						{
							$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
						}


						$calculations['prima_vac_esp']	= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
						$calculations['sdi']			= $t_nominaemployee->workerData->first()->sdi;
						$calculations['sd']				= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);

						$sumaSueldoTotal += round($t_nominaemployee->worked_days * $calculations['sd'],6);
						$calculations = [];
					}

					// -------------------------------------------------------------------------------------------------------
					foreach ($nominas as $t_nominaemployee) 
					{ 
						$calculationsNetIncome					= [];
						$calculationsNetIncome['fechaIngreso']	= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
						$calculationsNetIncome['fechaActual']	= Carbon::now();
						$calculationsNetIncome['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculationsNetIncome['fechaIngreso'],$calculationsNetIncome['fechaActual']);
						$calculationsNetIncome['yearsWork']		= ceil($calculationsNetIncome['diasTrabajados']/365);
						if ($calculationsNetIncome['yearsWork'] > 24) 
						{
							$calculationsNetIncome['vacationDays']	= 20;
						}
						else
						{
							$calculationsNetIncome['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculationsNetIncome['yearsWork'])->where('toYear','>=',$calculationsNetIncome['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculationsNetIncome['yearsWork'])->where('toYear','>=',$calculationsNetIncome['yearsWork'])->first()->days : 0;
						}

						//------------------------------------------------------------------
						
						$calculationsNetIncome['prima_vac_esp']		= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
						switch ($t_nominaemployee->idCatPeriodicity) 
						{
							case '02':
								$calculationsNetIncome['divisor'] = 7;
								break;

							case '04':
								$calculationsNetIncome['divisor'] = 15;
								break;

							case '05':
								$d = new DateTime(Carbon::now());
								$calculationsNetIncome['divisor'] = App\Http\Controllers\AdministracionNominaController::days_month($d->format('m'),$d->format('Y'));
								break;
							
							default:
								break;
						}

						$calculationsNetIncome['calc_sdi']				= $t_nominaemployee->total/$calculationsNetIncome['divisor'];
						$calculationsNetIncome['sdi']					= $calculationsNetIncome['calc_sdi'];
						$calculationsNetIncome['sd']					= round($calculationsNetIncome['sdi']/((($calculationsNetIncome['vacationDays']*$calculationsNetIncome['prima_vac_esp'])+15+365)/365),2);
						
						$calculationsNetIncome['diasTrabajadosM']		= $t_nominaemployee->worked_days;
						$calculationsNetIncome['sueldoTotal']			= round($calculationsNetIncome['diasTrabajadosM'] * $calculationsNetIncome['sd'],6);
						
						$calculationsNetIncome['sumaDiasTrabajados']	= $sumaDiasTrabajados;
						$calculationsNetIncome['sumaSueldoTotal']		= $sumaSueldoTotal;
						
						$calculationsNetIncome['uma']					= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
						$calculationsNetIncome['exento']				= $calculationsNetIncome['uma']*15; 
						
						$calculationsNetIncome['ptuPorPagar']			= round(App\Nomina::find($idnomina)->ptu_to_pay,6);
						$calculationsNetIncome['factorPorDias']			= round(($calculationsNetIncome['ptuPorPagar']/2)/$calculationsNetIncome['sumaDiasTrabajados'],6);
						$calculationsNetIncome['factorPorSueldo']		= round(($calculationsNetIncome['ptuPorPagar']/2)/$calculationsNetIncome['sumaSueldoTotal'],6);
						
						$calculationsNetIncome['ptuPorDias']			= round($calculationsNetIncome['diasTrabajadosM'] * $calculationsNetIncome['factorPorDias'],6);
						$calculationsNetIncome['ptuPorSueldos']			= round($calculationsNetIncome['sueldoTotal']*$calculationsNetIncome['factorPorSueldo'],6);
						$calculationsNetIncome['ptuTotal']				= round($calculationsNetIncome['ptuPorDias']+$calculationsNetIncome['ptuPorSueldos'],6);

						//-------- PERCEPCIOONES -------------------------------------------------------------

						$calculationsNetIncome['ptuExenta']			= $calculationsNetIncome['exento'];
						$calculationsNetIncome['ptuGravada']		= round($calculationsNetIncome['ptuTotal']-$calculationsNetIncome['ptuExenta'],6);
						$calculationsNetIncome['totalPercepciones']	= round($calculationsNetIncome['ptuExenta'],2)+round($calculationsNetIncome['ptuGravada'],2);
						$calculationsNetIncome['netIncome']			= $calculationsNetIncome['totalPercepciones'];

						// ----- calculo para dias de vacaciones ---------------------------
						$calculations					= [];
						$calculations['fechaIngreso']	= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
						$calculations['fechaActual']	= Carbon::now();
						$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
						$calculations['yearsWork']		= ceil($calculations['diasTrabajados']/365);
						if ($calculations['yearsWork'] > 24) 
						{
							$calculations['vacationDays']	= 20;
						}
						else
						{
							$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
						}

						//------------------------------------------------------------------
						
						$calculations['prima_vac_esp']		= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
						$calculations['sdi']				= $t_nominaemployee->workerData->first()->sdi;
						$calculations['sd']					= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);
						
						$calculations['diasTrabajadosM']	= $t_nominaemployee->worked_days;
						$calculations['sueldoTotal']		= round($calculations['diasTrabajadosM'] * $calculations['sd'],6);
						
						$calculations['sumaDiasTrabajados']	= $sumaDiasTrabajados;
						$calculations['sumaSueldoTotal']	= $sumaSueldoTotal;

						$calculations['uma']	= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
						$calculations['exento']	= $calculations['uma']*15; 

						$calculations['ptuPorPagar']		= round(App\Nomina::find($idnomina)->ptu_to_pay,6);
						$calculations['factorPorDias']		= round(($calculations['ptuPorPagar']/2)/$calculations['sumaDiasTrabajados'],6);
						$calculations['factorPorSueldo']	= round(($calculations['ptuPorPagar']/2)/$calculations['sumaSueldoTotal'],6);

						$calculations['ptuPorDias']		= round($calculations['diasTrabajadosM'] * $calculations['factorPorDias'],6);
						$calculations['ptuPorSueldos']	= round($calculations['sueldoTotal']*$calculations['factorPorSueldo'],6);
						$calculations['ptuTotal']		= round($calculations['ptuPorDias']+$calculations['ptuPorSueldos'],6);

						//-------- PERCEPCIOONES -------------------------------------------------------------

						$calculations['ptuExenta'] = $calculations['exento'];
						$calculations['ptuGravada'] = round($calculations['ptuTotal']-$calculations['ptuExenta'],6);
						$calculations['totalPercepciones'] = round($calculations['ptuExenta'],2)+round($calculations['ptuGravada'],2);


						// ------------------------------------------------------------------------------------

						//-------- RETENCIONES ----------------------------------------------------------------

						// ISR 1ER FRACCION

						$calculations['baseISR_fraccion1']			= round((($calculations['ptuGravada']/365)*30.4)+($calculations['sd']*30),6);
						$parameterISRF1								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion1'])->where('lapse',30)->get();

						$calculations['limiteInferior_fraccion1']	= $parameterISRF1->last()->inferior;
						$calculations['excedente_fraccion1']		= round($calculations['baseISR_fraccion1']-$calculations['limiteInferior_fraccion1'],6);
						$calculations['factor_fraccion1']			= round($parameterISRF1->last()->excess/100,6);
						$calculations['isrMarginal_fraccion1']		= round($calculations['excedente_fraccion1'] * $calculations['factor_fraccion1'],6);
						$calculations['cuotaFija_fraccion1']		= round($parameterISRF1->last()->quota,6);
						$calculations['isr_fraccion1']				= round($calculations['isrMarginal_fraccion1']+$calculations['cuotaFija_fraccion1'],6);

						// ISR 2DA FRACCION

						$calculations['baseISR_fraccion2']			= round($calculations['sd']*30,6);
						$parameterISRF2								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion2'])->where('lapse',30)->get();

						$calculations['limiteInferior_fraccion2']	= $parameterISRF2->last()->inferior;
						$calculations['excedente_fraccion2']		= round($calculations['baseISR_fraccion2']-$calculations['limiteInferior_fraccion2'],6);
						$calculations['factor_fraccion2']			= round($parameterISRF2->last()->excess/100,6);
						$calculations['isrMarginal_fraccion2']		= round($calculations['excedente_fraccion2'] * $calculations['factor_fraccion2'],6);
						$calculations['cuotaFija_fraccion2']		= round($parameterISRF2->last()->quota,6);
						$calculations['isr_fraccion2']				= round($calculations['isrMarginal_fraccion2']+$calculations['cuotaFija_fraccion2'],6);

						$calculations['resta']		= round($calculations['isr_fraccion1']-$calculations['isr_fraccion2'],6);
						$calculations['factor1']	= round((($calculations['ptuGravada']/365) * 30.4),6);
						if($calculations['factor1'] == 0)
						{
							$calculations['factor2']	= 0;
						}
						else
						{
							$calculations['factor2']	= round($calculations['resta']/$calculations['factor1'],6);
						}
						
						$calculations['isr']		= round($calculations['factor2']*$calculations['ptuGravada'],6);

						if ($t_nominaemployee->workerData->first()->alimonyDiscountType != '') 
						{
							$calculations['totalRetencionesTemp']  	= round($calculations['isr'],2);
						
							$calculations['netIncomeTemp']			= round($calculations['totalPercepciones']-$calculations['totalRetencionesTemp'],2);

							switch ($t_nominaemployee->workerData->first()->alimonyDiscountType) 
							{
								case 1: //monto
									$calculations['amountAlimony']	= $t_nominaemployee->workerData->first()->alimonyDiscount;
									$calculations['alimony']		= $calculations['amountAlimony'];
									break;

								case 2: // porcentaje
									$calculations['amountAlimony']	= $t_nominaemployee->workerData->first()->alimonyDiscount;
									$calculations['alimony']		= ($calculations['netIncomeTemp']*$calculations['amountAlimony'])/100;
									break;
								default:
									# code...
									break;
							}

							$calculations['totalRetenciones']	= round(round($calculations['isr'],2)+round($calculations['alimony'],2),2);
							$calculations['netIncome']			= round($calculations['totalPercepciones']-$calculations['totalRetenciones'],2);
						}
						else
						{ 
							$calculations['alimony']          = 0;
							$calculations['totalRetenciones'] = round($calculations['isr'],2);
							$calculations['netIncome']        = round($calculations['totalPercepciones']-$calculations['totalRetenciones'],2);
						}

						// --------------------------------------------------------------------------------------------

						$t_profitsharing						= App\ProfitSharing::find($t_nominaemployee->profitSharing->first()->idprofitSharing);
						$t_profitsharing->idnominaEmployee		= $t_nominaemployee->idnominaEmployee;
						$t_profitsharing->sd					= $calculations['sd'];
						$t_profitsharing->sdi					= $calculations['sdi'];
						$t_profitsharing->workedDays			= $calculations['diasTrabajadosM'];
						$t_profitsharing->totalSalary			= $calculations['sueldoTotal'];
						$t_profitsharing->ptuForDays			= $calculations['ptuPorDias'];
						$t_profitsharing->ptuForSalary			= $calculations['ptuPorSueldos'];
						$t_profitsharing->totalPtu				= $calculations['ptuTotal'];
						$t_profitsharing->exemptPtu				= $calculations['ptuExenta'];
						$t_profitsharing->taxedPtu				= $calculations['ptuGravada'];
						$t_profitsharing->totalPerceptions		= $calculations['totalPercepciones'];
						$t_profitsharing->isrRetentions			= $calculations['isr'];
						$t_profitsharing->alimony 				= $calculations['alimony'];
						$t_profitsharing->totalRetentions		= $calculations['totalRetenciones'];
						$t_profitsharing->netIncome				= $calculations['netIncome'];
						$t_profitsharing->totalIncomePS 		= $calculationsNetIncome['netIncome'];
						$t_profitsharing->save();

						$new_total				= round($t_profitsharing->totalIncomePS - $t_profitsharing->netIncome,2);
						$totalIncomePS			= $t_profitsharing->totalIncomePS;
						$calculations			= [];
						$calculationsNetIncome	= [];
						$t_nomina				= App\Nomina::find($idnomina);
						
						$req	= App\RequestModel::find($t_nomina->idFolio);
						$rfn	= App\RequestModel::where('kind',16)
								->where('idprenomina',$req->idprenomina)
								->where('idDepartment',$req->idDepartment)
								->where('taxPayment',0)
								->first();
						if ($rfn != '') 
						{
							$nominaemp = App\NominaEmployee::where('idrealEmployee',$t_nominaemployee->idrealEmployee)
									->where('idnomina',$rfn->nominasReal->first()->idnomina)
									->first();
							if ($nominaemp != '' || $nominaemp != null) 
							{
								$t_nominaemployee_nf = App\NominaEmployeeNF::where('idnominaEmployee',$nominaemp->idnominaEmployee)->first();
								if ($t_nominaemployee_nf != '' || $t_nominaemployee_nf != null) 
								{
									App\DiscountsNomina::where('idnominaemployeenf',$t_nominaemployee_nf->idnominaemployeenf)->delete();
									App\ExtrasNomina::where('idnominaemployeenf',$t_nominaemployee_nf->idnominaemployeenf)->delete();

									$t_nominaemployee_nf->netIncome			= $totalIncomePS;
									$t_nominaemployee_nf->complementPartial	= $new_total;
									$t_nominaemployee_nf->amount			= $new_total;
									$t_nominaemployee_nf->save();
								}
							}
						}
					}
					$totalRequest	= 0;
					$t_nomina		= App\Nomina::find($idnomina);
					foreach ($t_nomina->nominaEmployee as $n) 
					{
						$totalRequest += $n->profitSharing->first()->netIncome + $n->profitSharing->first()->alimony;
					}
					$t_nomina->amount			= $totalRequest;
					$t_nomina->save();

					return true;
					break;
			
			default:
				# code...
				break;
		}
	}

	public function salaryUpdate(Request $request)
	{
		if(Auth::user()->module->whereIn('id',[168,169,170])->count()>0) 
		{
			if ($request->file('csv_file')->isValid() && $request->file('csv_file')->getClientOriginalExtension() == "csv")
			{
				$delimiters = ["," => 0, ";" => 0];
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
					$nomina	= App\Nomina::find($request->idnomina);
					$folio	= $nomina->idFolio;
					$req	= App\RequestModel::find($folio);

					$nom_no_fiscal 	= App\RequestModel::where('kind',16)
									->where('idprenomina',$req->idprenomina)
									->where('idDepartment',$req->idDepartment)
									->where('taxPayment',0)
									->get();

					$name   = '/update_nomina/AdG_salary'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
					\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file')),'UTF-8'));
					$path   = \Storage::disk('reserved')->path($name);
					$csvArr = array();
					if(($handle = fopen($path, "r")) !== FALSE) 
					{
						$count = 0;
						while(($data = fgetcsv($handle,1000,$request->separator)) !== FALSE) 
						{
							if($count > 0) 
							{
								$csvArr[] = $data;
							}
							$count++;
						}
						fclose($handle);
					}
					array_walk($csvArr, function(&$a) use($csvArr)
					{
						$a = array_combine($csvArr[0], $a);
					});
					
					array_shift($csvArr);
					
					$headers = 
					[
						'id','empleado','curp','empresa','registro_patronal','monto_infonavit','sueldo_neto','sd','sdi','dias_trabajados','faltas','horas_extra','dias_festivos','domingos_trabajados','periodicidad','rango_de_fechas','dias_para_imss','forma_pago','alias','banco','clabe','cuenta','tarjeta','sucursal','sueldo','prestamo_percepcion','puntualidad','asistencia','tiempo_extra_exento','tiempo_extra_gravado','dias_festivos_exento','dia_festivo_gravado','prima_dominical_exenta','prima_dominical_gravada','subsidio','subsidio_causado','total_de_percepciones','imss','infonavit','fonacot','prestamo_retencion','retencion_de_isr','pension_alimenticia','otra_retencion_concepto','otra_retencion_importe','total_de_retenciones','sueldo_neto_fiscal','salario_base','dias_del_periodo_mensual','dias_del_periodo_bimestral','uma','prima_de_riesgo','cuota_fija','excedente','prestaciones_en_dinero','gastos_medicos_pensionados','riesgo_de_trabajo','invalidez_y_vida_patronal','guardirias_y_prestaciones','seguro_y_retiro','cesantia_y_vejez','infonavit_patronal','imss_mensual','rcv_bimestral','infonavit_bimestral'
					];
					

					if(empty($csvArr) || array_diff($headers, array_keys($csvArr[0])))
					{
						$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
						return back()->with('alert',$alert);	
					}

					$sumatoria	= 0;
					$errors		= 0;
					foreach ($csvArr as $key => $e) 
					{
						if(isset($e['sd']) && isset($e['sdi']) && isset($e['dias_trabajados']) && isset($e['dias_para_imss']) && isset($e['sueldo']) && isset($e['prestamo_percepcion']) && isset($e['puntualidad']) && isset($e['asistencia']) && isset($e['subsidio_causado']) && isset($e['subsidio']) && isset($e['total_de_percepciones']) && isset($e['imss']) && isset($e['infonavit']) && isset($e['fonacot']) && isset($e['prestamo_retencion']) && isset($e['otra_retencion_concepto']) && isset($e['otra_retencion_importe']) && isset($e['retencion_de_isr']) && isset($e['pension_alimenticia']) && isset($e['total_de_retenciones']) && isset($e['sueldo_neto']))
						{ 

							try 
							{
								$nominaEmp	= App\NominaEmployee::where('idnomina',$request->idnomina)->where('idrealEmployee',$e['id'])->first();
								if ($nominaEmp != "" && $nominaEmp->salary()->exists()) 
								{
									$nominaEmp->sundays					= empty($e['domingos_trabajados']) ? 0 : ($e['domingos_trabajados'] > 0 ? $e['domingos_trabajados'] : 0);
									$nominaEmp->absence					= empty($e['faltas']) ? 0 : ($e['faltas'] > 0 ? $e['faltas'] : 0);
									$t_salary							= App\Salary::find($nominaEmp->salary->first()->idSalary);
									$t_salary->sundays					= empty($e['domingos_trabajados']) ? 0 : ($e['domingos_trabajados'] > 0 ? $e['domingos_trabajados'] : 0);
									$t_salary->sd						= empty($e['sd']) ? 0 : ($e['sd'] > 0 ? $e['sd'] : 0);
									$t_salary->sdi						= empty($e['sdi']) ? 0 : ($e['sdi'] > 0 ? $e['sdi'] : 0);
									$t_salary->workedDays				= empty($e['dias_trabajados']) ? 0 : ($e['dias_trabajados'] > 0 ? $e['dias_trabajados'] : 0);
									$t_salary->daysForImss				= empty($e['dias_para_imss']) ? 0 : ($e['dias_para_imss'] > 0 ? $e['dias_para_imss'] : 0);
									$t_salary->salary					= empty($e['sueldo']) ? 0 : ($e['sueldo'] > 0 ? $e['sueldo'] : 0);
									$t_salary->loan_perception			= empty($e['prestamo_percepcion']) ? 0 : ($e['prestamo_percepcion'] > 0 ? $e['prestamo_percepcion'] : 0);
									$t_salary->puntuality				= empty($e['puntualidad']) ? 0 : ($e['puntualidad'] > 0 ? $e['puntualidad'] : 0);
									$t_salary->assistance				= empty($e['asistencia']) ? 0 : ($e['asistencia'] > 0 ? $e['asistencia'] : 0);
									$t_salary->extra_hours				= empty($e['horas_extra']) ? 0 : ($e['horas_extra'] > 0 ? $e['horas_extra'] : 0);
									$nominaEmp->extra_hours				= empty($e['horas_extra']) ? 0 : ($e['horas_extra'] > 0 ? $e['horas_extra'] : 0);
									$nominaEmp->holidays				= empty($e['dias_festivos']) ? 0 : ($e['dias_festivos'] > 0 ? $e['dias_festivos'] : 0);
									$t_salary->extra_time				= empty($e['tiempo_extra']) ? 0 : ($e['tiempo_extra'] > 0 ? $e['tiempo_extra'] : 0);
									$t_salary->holidays					= empty($e['dias_festivos']) ? 0 : ($e['dias_festivos'] > 0 ? $e['dias_festivos'] : 0);
									$t_salary->holiday					= empty($e['dia_festivo']) ? 0 : ($e['dia_festivo'] > 0 ? $e['dia_festivo'] : 0);
									$t_salary->subsidyCaused			= empty($e['subsidio_causado']) ? 0 : ($e['subsidio_causado'] > 0 ? $e['subsidio_causado'] : 0);
									$t_salary->subsidy					= empty($e['subsidio']) ? 0 : ($e['subsidio'] > 0 ? $e['subsidio'] : 0);
									$t_salary->totalPerceptions			= empty($e['total_de_percepciones']) ? 0 : ($e['total_de_percepciones'] > 0 ? $e['total_de_percepciones'] : 0);
									$t_salary->imss						= empty($e['imss']) ? 0 : ($e['imss'] > 0 ? $e['imss'] : 0);
									$t_salary->infonavit				= empty($e['infonavit']) ? 0 : ($e['infonavit'] > 0 ? $e['infonavit'] : 0);
									$t_salary->fonacot					= empty($e['fonacot']) ? 0 : ($e['fonacot'] > 0 ? $e['fonacot'] : 0);
									$t_salary->loan_retention			= empty($e['prestamo_retencion']) ? 0 : ($e['prestamo_retencion'] > 0 ? $e['prestamo_retencion'] : 0);
									$t_salary->other_retention_concept	= empty($e['otra_retencion_concepto']) ? 0 : ($e['otra_retencion_concepto'] > 0 ? $e['otra_retencion_concepto'] : 0);
									$t_salary->other_retention_amount	= empty($e['otra_retencion_importe']) ? 0 : ($e['otra_retencion_importe'] > 0 ? $e['otra_retencion_importe'] : 0);
									$t_salary->isrRetentions			= empty($e['retencion_de_isr']) ? 0 : ($e['retencion_de_isr'] > 0 ? $e['retencion_de_isr'] : 0);
									$t_salary->alimony					= empty($e['pension_alimenticia']) ? 0 : ($e['pension_alimenticia'] > 0 ? $e['pension_alimenticia'] : 0);
									$t_salary->totalRetentions			= empty($e['total_de_retenciones']) ? 0 : ($e['total_de_retenciones'] > 0 ? $e['total_de_retenciones'] : 0);
									$t_salary->netIncome				= empty($e['sueldo_neto_fiscal']) ? 0 : ($e['sueldo_neto_fiscal'] > 0 ? $e['sueldo_neto_fiscal'] : 0);
									$t_salary->risk_number				= empty($e['prima_de_riesgo']) ? 0 : ($e['prima_de_riesgo'] > 0 ? $e['prima_de_riesgo'] : 0);
									$t_salary->uma						= empty($e['uma']) ? 0 : ($e['uma'] > 0 ? $e['uma'] : 0);
									$t_salary->extra_time				= empty($e['tiempo_extra_exento']) ? 0 : (($e['tiempo_extra_exento']+$e['tiempo_extra_gravado']) > 0 ? ($e['tiempo_extra_exento']+$e['tiempo_extra_gravado']) : 0);
									$t_salary->extra_time_taxed			= empty($e['tiempo_extra_gravado']) ? 0 : ($e['tiempo_extra_gravado'] > 0 ? $e['tiempo_extra_gravado'] : 0);
									$t_salary->holiday					= empty($e['dias_festivos_exento']) ? 0 : (($e['dias_festivos_exento']+$e['dia_festivo_gravado']) > 0 ? ($e['dias_festivos_exento']+$e['dia_festivo_gravado']) : 0);
									$t_salary->holiday_taxed			= empty($e['dia_festivo_gravado']) ? 0 : ($e['dia_festivo_gravado'] > 0 ? $e['dia_festivo_gravado'] : 0);
									$t_salary->exempt_sunday			= empty($e['prima_dominical_exenta']) ? 0 : ($e['prima_dominical_exenta'] > 0 ? $e['prima_dominical_exenta'] : 0);
									$t_salary->taxed_sunday				= empty($e['prima_dominical_gravada']) ? 0 : ($e['prima_dominical_gravada'] > 0 ? $e['prima_dominical_gravada'] : 0);
									$nominaEmp->save();
									$t_salary->save();
									$sumatoria += $t_salary->netIncome+$t_salary->alimony;

									if($nom_no_fiscal != '')
									{
										foreach ($nom_no_fiscal as $request_nf) 
										{
											$nom_emp_nf = App\NominaEmployee::where('idrealEmployee',$e['id'])
														->where('idnomina',$request_nf->nominasReal->first()->idnomina)
														->first();

											if ($nom_emp_nf != "") 
											{
												if ($request_nf->status == 2) 
												{
													$nom_emp_nf->sundays		= empty($e['domingos_trabajados']) ? 0 : ($e['domingos_trabajados'] > 0 ? $e['domingos_trabajados'] : 0);
													$nom_emp_nf->extra_hours	= empty($e['horas_extra']) ? 0 : ($e['horas_extra'] > 0 ? $e['horas_extra'] : 0);
													$nom_emp_nf->holidays		= empty($e['dias_festivos']) ? 0 : ($e['dias_festivos'] > 0 ? $e['dias_festivos'] : 0);
													$nom_emp_nf->absence		= empty($e['faltas']) ? null : ($e['faltas'] > 0 ? $e['faltas'] : 0);
												}

												$idnominaemployeenf 	= $nom_emp_nf->nominasEmployeeNF()->exists() ? $nom_emp_nf->nominasEmployeeNF->first()->idnominaemployeenf : "";

												if ($idnominaemployeenf != "") 
												{
													$nomina_nf 				= App\NominaEmployeeNF::find($idnominaemployeenf);
													$nomina_nf->netIncome 	= $nomina_nf->amount + $t_salary->netIncome;
													$nomina_nf->save();
												}
											}

										}
									}	
								}
							} 
							catch (Exception $e) 
							{
								$errors++;
							}
						}
						else
						{
							$errors++;
						}
					}
					$nomina = App\Nomina::find($request->idnomina);
					if ($sumatoria > 0) 
					{
						$nomina->amount	= $sumatoria;
					}
					$nomina->save();

					if ($errors > 0) 
					{
						$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
					}
					else
					{
						$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
					}
				}
				else
				{
					$alert	= "swal('','".lang::get("messages.separator_error")."', 'error');";
				}
				return back()->with('alert',$alert);
			}
			else
			{
				$alert	= "swal('', '".Lang::get("messages.extension_allowed",["param" => 'CSV'])."', 'error');";
				return back()->with('alert',$alert);
			}
		}
	}

	public function bonusUpdate(Request $request)
	{
		if (Auth::user()->module->whereIn('id',[168,169,170])->count()>0) 
		{
			if ($request->file('csv_file')->isValid() && $request->file('csv_file')->getClientOriginalExtension() == "csv") 
			{
				$delimiters = ["," => 0, ";" => 0];
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
					$name 		= '/update_nomina/AdG_bonus'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
					\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file')),'UTF-8'));
					$path		= \Storage::disk('reserved')->path($name);
					$csvArr		= array();
					if (($handle = fopen($path, "r")) !== FALSE) 
					{
						$count = 0;
						while (($data = fgetcsv($handle,1000,$request->separator)) !== FALSE) 
						{
							if ($count > 0) 
							{
								$csvArr[] = $data;
							}
							$count++;
						}
						fclose($handle);
					}
					array_walk($csvArr, function(&$a) use($csvArr)
					{
						$a = array_combine($csvArr[0], $a);
					});
					array_shift($csvArr);
					$sumatoria	= 0;
					$errors		= 0;

					$headers = 
					[
						'id',
						'empleado',
						'sd',
						'sdi',
						'fecha_de_ingreso',
						'dias_para_aguinaldo',
						'parte_proporcional_para_aguinaldo',
						'forma_pago',
						'alias',
						'banco',
						'clabe',
						'cuenta',
						'tarjeta',
						'sucursal',
						'aguinaldo_exento',
						'aguinaldo_gravable',
						'total_de_percepciones',
						'isr',
						'pension_alimenticia',
						'total_de_retenciones',
						'sueldo_neto',
					];
					

					if(empty($csvArr) || array_diff($headers, array_keys($csvArr[0])))
					{
						$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
						return back()->with('alert',$alert);	
					}

					foreach($csvArr as $key => $e)
					{
						if(isset($e['sd']) && isset($e['sdi']) && isset($e['fecha_de_ingreso']) && isset($e['dias_para_aguinaldo']) && isset($e['parte_proporcional_para_aguinaldo']) && isset($e['aguinaldo_exento']) && isset($e['aguinaldo_gravable']) && isset($e['total_de_percepciones']) && isset($e['isr']) && isset($e['pension_alimenticia']) && isset($e['total_de_retenciones']) && isset($e['sueldo_neto'])) 
						{
							$nominaEmp 	= App\NominaEmployee::where('idnomina',$request->idnomina)->where('idrealEmployee',$e['id'])->first();
							if ($nominaEmp != "" && $nominaEmp->bonus()->exists()) 
							{
								$t_bonus									= App\Bonus::find($nominaEmp->bonus->first()->idBonus);
								$t_bonus->sd								= empty($e['sd']) ? null : $e['sd'];
								$t_bonus->sdi								= empty($e['sdi']) ? null : $e['sdi'];
								$t_bonus->dateOfAdmission					= empty($e['fecha_de_ingreso']) ? null : $e['fecha_de_ingreso'];
								$t_bonus->daysForBonuses					= empty($e['dias_para_aguinaldo']) ? null : $e['dias_para_aguinaldo'];
								$t_bonus->proportionalPartForChristmasBonus	= empty($e['parte_proporcional_para_aguinaldo']) ? null : $e['parte_proporcional_para_aguinaldo'];
								$t_bonus->exemptBonus						= empty($e['aguinaldo_exento']) ? null : $e['aguinaldo_exento'];
								$t_bonus->taxableBonus						= empty($e['aguinaldo_gravable']) ? null : $e['aguinaldo_gravable'];
								$t_bonus->totalPerceptions					= empty($e['total_de_percepciones']) ? null : $e['total_de_percepciones'];
								$t_bonus->isr								= empty($e['isr']) ? null : $e['isr'];
								$t_bonus->alimony							= empty($e['pension_alimenticia']) ? null : $e['pension_alimenticia'];
								$t_bonus->totalTaxes						= empty($e['total_de_retenciones']) ? null : $e['total_de_retenciones'];
								$t_bonus->netIncome							= empty($e['sueldo_neto']) ? null : $e['sueldo_neto'];
								$t_bonus->save();
								$sumatoria += $t_bonus->netIncome;
							}
						}
						else
						{
							$errors++;
						}
					}
					$nomina = App\Nomina::find($request->idnomina);
					if ($sumatoria > 0) 
					{
						$nomina->amount	= $sumatoria;
					}
					$nomina->save();

					if ($errors > 0) 
					{
						$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
					}
					else
					{
						$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
					}
				}
				else
				{
					$alert	= "swal('','".lang::get("messages.separator_error")."', 'error');";
				}
				return back()->with('alert',$alert);
			}
			else
			{
				$alert	= "swal('', '".Lang::get("messages.extension_allowed",["param" => 'CSV'])."', 'error');";
				return back()->with('alert',$alert);
			}
		}
	}

	public function liquidationUpdate(Request $request)
	{
		if (Auth::user()->module->whereIn('id',[168,169,170])->count()>0) 
		{
			if ($request->file('csv_file')->isValid() && $request->file('csv_file')->getClientOriginalExtension() == "csv") 
			{
				$delimiters = ["," => 0, ";" => 0];
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
					$name 		= '/update_nomina/AdG_liquidacion'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
					\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file')),'UTF-8'));
					$path		= \Storage::disk('reserved')->path($name);
					$csvArr		= array();
					if (($handle = fopen($path, "r")) !== FALSE) 
					{
						$count = 0;
						while (($data = fgetcsv($handle,1000,$request->separator)) !== FALSE) 
						{
							if ($count > 0) 
							{
								$csvArr[] = $data;
							}
							$count++;
						}
						fclose($handle);
					}
					array_walk($csvArr, function(&$a) use($csvArr)
					{
						$a = array_combine($csvArr[0], $a);
					});
					array_shift($csvArr);
					$sumatoria	= 0;
					$errors		= 0;

					$headers = 
					[
						'id',
						'empleado',
						'sd',
						'sdi',
						'fecha_de_ingreso',
						'fecha_de_baja',
						'anios_completos',
						'dias_trabajados',
						'dias_para_vacaciones',
						'dias_para_aguinaldo',
						'forma_pago',
						'alias',
						'banco',
						'clabe',
						'cuenta',
						'tarjeta',
						'sucursal',
						'sueldo_por_liquidacion',
						'20_dias_por_anio_de_servicio',
						'prima_de_antiguedad',
						'indemnizacion_exenta',
						'indemnizacion_gravada',
						'vacaciones',
						'aguinaldo_exento',
						'aguinaldo_gravable',
						'prima_vacacional_exenta',
						'prima_vacacional_gravada',
						'otras_percepciones',
						'total_de_percepciones',
						'isr',
						'pension_alimenticia',
						'otras_retenciones',
						'total_de_retenciones',
						'sueldo_neto'
					];
					

					if(empty($csvArr) || array_diff($headers, array_keys($csvArr[0])))
					{
						$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
						return back()->with('alert',$alert);	
					}

					foreach ($csvArr as $key => $e) 
					{
						if (isset($e['sd']) && isset($e['sdi']) && isset($e['fecha_de_ingreso']) && isset($e['fecha_de_baja']) && isset($e['anios_completos']) && isset($e['dias_trabajados']) && isset($e['dias_para_vacaciones']) && isset($e['dias_para_aguinaldo']) && isset($e['sueldo_por_liquidacion']) && isset($e['20_dias_por_anio_de_servicio']) && isset($e['prima_de_antiguedad']) && isset($e['indemnizacion_exenta']) && isset($e['indemnizacion_gravada']) && isset($e['vacaciones']) && isset($e['aguinaldo_exento']) && isset($e['aguinaldo_gravable']) && isset($e['prima_vacacional_exenta']) && isset($e['prima_vacacional_gravada']) && isset($e['otras_percepciones']) && isset($e['total_de_percepciones']) && isset($e['isr']) && isset($e['pension_alimenticia']) && isset($e['total_de_retenciones']) && isset($e['sueldo_neto'])) 
						{
							$nominaEmp	= App\NominaEmployee::where('idnomina',$request->idnomina)->where('idrealEmployee',$e['id'])->first();
							if ($nominaEmp != "" && $nominaEmp->liquidation()->exists()) 
							{
								$t_liquidation								= App\Liquidation::find($nominaEmp->liquidation->first()->idLiquidation);
								$t_liquidation->sd							= empty($e['sd']) ? null : $e['sd'];
								$t_liquidation->sdi							= empty($e['sdi']) ? null : $e['sdi'];
								$t_liquidation->admissionDate				= empty($e['fecha_de_ingreso']) ? null : $e['fecha_de_ingreso'];
								$t_liquidation->downDate					= empty($e['fecha_de_baja']) ? null : $e['fecha_de_baja'];
								$t_liquidation->fullYears					= empty($e['anios_completos']) ? '0' : $e['anios_completos'];
								$t_liquidation->workedDays					= empty($e['dias_trabajados']) ? null : $e['dias_trabajados'];
								$t_liquidation->holidayDays					= empty($e['dias_para_vacaciones']) ? null : $e['dias_para_vacaciones'];
								$t_liquidation->bonusDays					= empty($e['dias_para_aguinaldo']) ? null : $e['dias_para_aguinaldo'];
								$t_liquidation->liquidationSalary			= empty($e['sueldo_por_liquidacion']) ? null : $e['sueldo_por_liquidacion'];
								$t_liquidation->twentyDaysPerYearOfServices	= empty($e['20_dias_por_anio_de_servicio']) ? null : $e['20_dias_por_anio_de_servicio'];
								$t_liquidation->seniorityPremium			= empty($e['prima_de_antiguedad']) ? null : $e['prima_de_antiguedad'];
								$t_liquidation->exemptCompensation			= empty($e['indemnizacion_exenta']) ? null : $e['indemnizacion_exenta'];
								$t_liquidation->taxedCompensation			= empty($e['indemnizacion_gravada']) ? null : $e['indemnizacion_gravada'];
								$t_liquidation->holidays					= empty($e['vacaciones']) ? null : $e['vacaciones'];
								$t_liquidation->exemptBonus					= empty($e['aguinaldo_exento']) ? null : $e['aguinaldo_exento'];
								$t_liquidation->taxableBonus				= empty($e['aguinaldo_gravable']) ? null : $e['aguinaldo_gravable'];
								$t_liquidation->holidayPremiumExempt		= empty($e['prima_vacacional_exenta']) ? null : $e['prima_vacacional_exenta'];
								$t_liquidation->holidayPremiumTaxed			= empty($e['prima_vacacional_gravada']) ? null : $e['prima_vacacional_gravada'];
								$t_liquidation->otherPerception				= empty($e['otras_percepciones']) ? null : $e['otras_percepciones'];
								$t_liquidation->totalPerceptions			= empty($e['total_de_percepciones']) ? null : $e['total_de_percepciones'];
								$t_liquidation->isr							= empty($e['isr']) ? null : $e['isr'];
								$t_liquidation->alimony						= empty($e['pension_alimenticia']) ? null : $e['pension_alimenticia'];
								$t_liquidation->other_retention				= empty($e['otras_retenciones']) ? null : $e['otras_retenciones'];
								$t_liquidation->totalRetentions				= empty($e['total_de_retenciones']) ? null : $e['total_de_retenciones'];
								$t_liquidation->netIncome					= empty($e['sueldo_neto']) ? null : $e['sueldo_neto'];
								$t_liquidation->save();

								$sumatoria += $t_liquidation->netIncome;
							}
						}
						else
						{
							$errors++;
						}
					}

					$nomina			= App\Nomina::find($request->idnomina);
					if ($sumatoria > 0) 
					{
						$nomina->amount	= $sumatoria;
					}
					$nomina->save();

					if ($errors > 0) 
					{
						$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
					}
					else
					{
						$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
					}
				}
				else
				{
					$alert	= "swal('','".lang::get("messages.separator_error")."', 'error');";
				}
				return back()->with('alert',$alert);
			}
			else
			{
				$alert	= "swal('', '".Lang::get("messages.extension_allowed",["param" => 'CSV'])."', 'error');";
				return back()->with('alert',$alert);
			}
		}
	}

	public function settlementUpdate(Request $request)
	{
		if (Auth::user()->module->whereIn('id',[168,169,170])->count()>0) 
		{
			if ($request->file('csv_file')->isValid() && $request->file('csv_file')->getClientOriginalExtension() == "csv") 
			{
				$delimiters = ["," => 0, ";" => 0];
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
					$name 		= '/update_nomina/AdG_finiquito'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
					\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file')),'UTF-8'));
					$path		= \Storage::disk('reserved')->path($name);
					$csvArr		= array();
					if (($handle = fopen($path, "r")) !== FALSE) 
					{
						$count = 0;
						while (($data = fgetcsv($handle,1000,$request->separator)) !== FALSE) 
						{
							if ($count > 0) 
							{
								$csvArr[] = $data;
							}
							$count++;
						}
						fclose($handle);
					}
					array_walk($csvArr, function(&$a) use($csvArr)
					{
						$a = array_combine($csvArr[0], $a);
					});
					array_shift($csvArr);
					$sumatoria	= 0;
					$errors		= 0;

					$headers = 
					[
						'id',
						'empleado',
						'sd',
						'sdi',
						'fecha_de_ingreso',
						'fecha_de_baja',
						'anios_completos',
						'dias_trabajados',
						'dias_para_vacaciones',
						'dias_para_aguinaldo',
						'forma_pago',
						'alias',
						'banco',
						'clabe',
						'cuenta',
						'tarjeta',
						'sucursal',
						'prima_de_antiguedad',
						'indemnizacion_exenta',
						'indemnizacion_gravada',
						'vacaciones',
						'aguinaldo_exento',
						'aguinaldo_gravable',
						'prima_vacacional_exenta',
						'prima_vacacional_gravada',
						'otras_percepciones',
						'total_de_percepciones',
						'isr',
						'pension_alimenticia',
						'otras_retenciones',
						'total_de_retenciones',
						'sueldo_neto'
					];
					

					if(empty($csvArr) || array_diff($headers, array_keys($csvArr[0])))
					{
						$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
						return back()->with('alert',$alert);	
					}

					foreach ($csvArr as $key => $e) 
					{
						if (isset($e['sd']) && isset($e['sdi']) && isset($e['fecha_de_ingreso']) && isset($e['fecha_de_baja']) && isset($e['anios_completos']) && isset($e['dias_trabajados']) && isset($e['dias_para_vacaciones']) && isset($e['dias_para_aguinaldo']) && isset($e['prima_de_antiguedad']) && isset($e['indemnizacion_exenta']) && isset($e['indemnizacion_gravada']) && isset($e['vacaciones']) && isset($e['aguinaldo_exento']) && isset($e['aguinaldo_gravable']) && isset($e['prima_vacacional_exenta']) && isset($e['prima_vacacional_gravada']) && isset($e['otras_percepciones']) && isset($e['total_de_percepciones']) && isset($e['isr']) && isset($e['pension_alimenticia']) && isset($e['total_de_retenciones']) && isset($e['sueldo_neto'])) 
						{
							$nominaEmp	= App\NominaEmployee::where('idnomina',$request->idnomina)->where('idrealEmployee',$e['id'])->first();
							if ($nominaEmp != "" && $nominaEmp->liquidation()->exists())
							{
								$t_liquidation							= App\Liquidation::find($nominaEmp->liquidation->first()->idLiquidation);
								$t_liquidation->sd						= empty($e['sd']) ? null : $e['sd'];
								$t_liquidation->sdi						= empty($e['sdi']) ? null : $e['sdi'];
								$t_liquidation->admissionDate			= empty($e['fecha_de_ingreso']) ? null : $e['fecha_de_ingreso'];
								$t_liquidation->downDate				= empty($e['fecha_de_baja']) ? null : $e['fecha_de_baja'];
								$t_liquidation->fullYears				= empty($e['anios_completos']) ? '0' : $e['anios_completos'];
								$t_liquidation->workedDays				= empty($e['dias_trabajados']) ? null : $e['dias_trabajados'];
								$t_liquidation->holidayDays				= empty($e['dias_para_vacaciones']) ? null : $e['dias_para_vacaciones'];
								$t_liquidation->bonusDays				= empty($e['dias_para_aguinaldo']) ? null : $e['dias_para_aguinaldo'];
								$t_liquidation->seniorityPremium		= empty($e['prima_de_antiguedad']) ? null : $e['prima_de_antiguedad'];
								$t_liquidation->exemptCompensation		= empty($e['indemnizacion_exenta']) ? null : $e['indemnizacion_exenta'];
								$t_liquidation->taxedCompensation		= empty($e['indemnizacion_gravada']) ? null : $e['indemnizacion_gravada'];
								$t_liquidation->holidays				= empty($e['vacaciones']) ? null : $e['vacaciones'];
								$t_liquidation->exemptBonus				= empty($e['aguinaldo_exento']) ? null : $e['aguinaldo_exento'];
								$t_liquidation->taxableBonus			= empty($e['aguinaldo_gravable']) ? null : $e['aguinaldo_gravable'];
								$t_liquidation->holidayPremiumExempt	= empty($e['prima_vacacional_exenta']) ? null : $e['prima_vacacional_exenta'];
								$t_liquidation->holidayPremiumTaxed		= empty($e['prima_vacacional_gravada']) ? null : $e['prima_vacacional_gravada'];
								$t_liquidation->otherPerception			= empty($e['otras_percepciones']) ? null : $e['otras_percepciones'];
								$t_liquidation->totalPerceptions		= empty($e['total_de_percepciones']) ? null : $e['total_de_percepciones'];
								$t_liquidation->isr						= empty($e['isr']) ? null : $e['isr'];
								$t_liquidation->alimony					= empty($e['pension_alimenticia']) ? null : $e['pension_alimenticia'];
								$t_liquidation->other_retention			= empty($e['otras_retenciones']) ? null : $e['otras_retenciones'];
								$t_liquidation->totalRetentions			= empty($e['total_de_retenciones']) ? null : $e['total_de_retenciones'];
								$t_liquidation->netIncome				= empty($e['sueldo_neto']) ? null : $e['sueldo_neto'];
								$t_liquidation->save();

								$sumatoria	+= $t_liquidation->netIncome;
							}
						}
						else
						{
							$errors++;
						}
					}
					$nomina			= App\Nomina::find($request->idnomina);
					if ($sumatoria > 0) 
					{
						$nomina->amount	= $sumatoria;
					}
					$nomina->save();

					if ($errors > 0) 
					{
						$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
					}
					else
					{
						$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
					}
				}
				else
				{
					$alert	= "swal('','".lang::get("messages.separator_error")."', 'error');";
				}
				return back()->with('alert',$alert);
			}
			else
			{
				$alert	= "swal('', '".Lang::get("messages.extension_allowed",["param" => 'CSV'])."', 'error');";
				return back()->with('alert',$alert);
			}
		}
	}

	public function vacationPremiumUpdate(Request $request)
	{
		if (Auth::user()->module->whereIn('id',[168,169,170])->count()>0) 
		{
			if ($request->file('csv_file')->isValid() && $request->file('csv_file')->getClientOriginalExtension() == "csv") 
			{
				$delimiters = ["," => 0, ";" => 0];
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
					$name 		= '/update_nomina/AdG_vacation_premium'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
					\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file')),'UTF-8'));
					$path		= \Storage::disk('reserved')->path($name);
					$csvArr		= array();
					if (($handle = fopen($path, "r")) !== FALSE) 
					{
						$count = 0;
						while (($data = fgetcsv($handle,1000,$request->separator)) !== FALSE) 
						{
							if ($count > 0) 
							{
								$csvArr[] = $data;
							}
							$count++;
						}
						fclose($handle);
					}
					array_walk($csvArr, function(&$a) use($csvArr)
					{
						$a = array_combine($csvArr[0], $a);
					});
					array_shift($csvArr);
					$sumatoria	= 0;
					$errors		= 0;

					$headers = 
					[
						'id',
						'empleado',
						'sd',
						'sdi',
						'fecha_de_ingreso',
						'dias_trabajados',
						'dias_para_vacaciones',
						'forma_pago',
						'alias',
						'banco',
						'clabe',
						'cuenta',
						'tarjeta',
						'sucursal',
						'vacaciones',
						'prima_vacacional_exenta',
						'prima_vacacional_gravada',
						'total_de_percepciones',
						'isr',
						'pension_alimenticia',
						'total_de_retenciones',
						'sueldo_neto'
					];
					

					if(empty($csvArr) || array_diff($headers, array_keys($csvArr[0])))
					{
						$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
						return back()->with('alert',$alert);	
					}

					foreach ($csvArr as $key => $e) 
					{
						if (isset($e['sd']) && isset($e['sdi']) && isset($e['dias_trabajados']) && isset($e['dias_para_vacaciones']) && isset($e['vacaciones']) && isset($e['prima_vacacional_exenta']) && isset($e['prima_vacacional_gravada']) && isset($e['total_de_percepciones']) && isset($e['isr']) && isset($e['pension_alimenticia']) && isset($e['total_de_retenciones']) && isset($e['sueldo_neto'])) 
						{
							$nominaEmp	= App\NominaEmployee::where('idnomina',$request->idnomina)->where('idrealEmployee',$e['id'])->first();
							if ($nominaEmp != "" && $nominaEmp->vacationPremium()->exists()) 
							{
								$t_vacationpremium							= App\VacationPremium::find($nominaEmp->vacationPremium->first()->idvacationPremium);
								$t_vacationpremium->sd						= empty($e['sd']) ? null : $e['sd'];
								$t_vacationpremium->sdi						= empty($e['sdi']) ? null : $e['sdi'];
								$t_vacationpremium->workedDays				= empty($e['dias_trabajados']) ? null : $e['dias_trabajados'];
								$t_vacationpremium->holidaysDays			= empty($e['dias_para_vacaciones']) ? null : $e['dias_para_vacaciones'];
								$t_vacationpremium->holidays				= empty($e['vacaciones']) ? null : $e['vacaciones'];
								$t_vacationpremium->exemptHolidayPremium	= empty($e['prima_vacacional_exenta']) ? null : $e['prima_vacacional_exenta'];
								$t_vacationpremium->holidayPremiumTaxed		= empty($e['prima_vacacional_gravada']) ? null : $e['prima_vacacional_gravada'];
								$t_vacationpremium->totalPerceptions		= empty($e['total_de_percepciones']) ? null : $e['total_de_percepciones'];
								$t_vacationpremium->isr						= empty($e['isr']) ? null : $e['isr'];
								$t_vacationpremium->alimony					= empty($e['pension_alimenticia']) ? null : $e['pension_alimenticia'];
								$t_vacationpremium->totalTaxes				= empty($e['total_de_retenciones']) ? null : $e['total_de_retenciones'];
								$t_vacationpremium->netIncome				= empty($e['sueldo_neto']) ? null : $e['sueldo_neto'];
								$t_vacationpremium->save();

								$sumatoria += $t_vacationpremium->netIncome;
							}
						}
						else
						{
							$errors++;
						}
					}
					
					$nomina			= App\Nomina::find($request->idnomina);
					if ($sumatoria > 0) 
					{
						$nomina->amount	= $sumatoria;
					}
					$nomina->save();

					if ($errors > 0) 
					{
						$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
					}
					else
					{
						$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
					}
				}
				else
				{
					$alert	= "swal('','".lang::get("messages.separator_error")."', 'error');";
				}
				return back()->with('alert',$alert);
			}
			else
			{
				$alert	= "swal('', '".Lang::get("messages.extension_allowed",["param" => 'CSV'])."', 'error');";
				return back()->with('alert',$alert);
			}
		}
	}

	public function profitSharingUpdate(Request $request)
	{
		if (Auth::user()->module->whereIn('id',[168,169,170])->count()>0) 
		{
			if ($request->file('csv_file')->isValid() && $request->file('csv_file')->getClientOriginalExtension() == "csv") 
			{
				$delimiters = ["," => 0, ";" => 0];
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
					$name 		= '/update_nomina/AdG_vacation_premium'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
					\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file')),'UTF-8'));
					$path		= \Storage::disk('reserved')->path($name);
					$csvArr		= array();
					if (($handle = fopen($path, "r")) !== FALSE) 
					{
						$count = 0;
						while (($data = fgetcsv($handle,1000,$request->separator)) !== FALSE) 
						{
							if ($count > 0) 
							{
								$csvArr[] = $data;
							}
							$count++;
						}
						fclose($handle);
					}
					array_walk($csvArr, function(&$a) use($csvArr)
					{
						$a = array_combine($csvArr[0], $a);
					});
					array_shift($csvArr);
					$sumatoria	= 0;
					$errors		= 0;

					$headers = 
					[
						'id',
						'empleado',
						'fecha_de_ingreso',
						'sd',
						'sdi',
						'dias_trabajados',
						'sueldo_total',
						'ptu_por_dias',
						'ptu_por_sueldos',
						'ptu_total',
						'forma_pago',
						'alias',
						'banco',
						'clabe',
						'cuenta',
						'tarjeta',
						'sucursal',
						'ptu_exenta',
						'ptu_gravada',
						'total_de_percepciones',
						'isr',
						'pension_alimenticia',
						'total_de_retenciones',
						'sueldo_neto'
					];
					

					if(empty($csvArr) || array_diff($headers, array_keys($csvArr[0])))
					{
						$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
						return back()->with('alert',$alert);	
					}

					foreach ($csvArr as $key => $e) 
					{
						if (isset($e['sd']) && isset($e['sdi']) && isset($e['dias_trabajados']) && isset($e['sueldo_total']) && isset($e['ptu_por_dias']) && isset($e['ptu_por_sueldos']) && isset($e['ptu_total']) && isset($e['ptu_exenta']) && isset($e['ptu_gravada']) && isset($e['total_de_percepciones']) && isset($e['isr']) && isset($e['pension_alimenticia']) && isset($e['total_de_retenciones']) && isset($e['sueldo_neto'])) 
						{
							$nominaEmp	= App\NominaEmployee::where('idnomina',$request->idnomina)->where('idrealEmployee',$e['id'])->first();
							if ($nominaEmp != "" && $nominaEmp->profitSharing()->exists()) 
							{
								$t_profitsharing					= App\ProfitSharing::find($nominaEmp->profitSharing->first()->idprofitSharing);
								$t_profitsharing->sd				= empty($e['sd']) ? null : $e['sd'];
								$t_profitsharing->sdi				= empty($e['sdi']) ? null : $e['sdi'];
								$t_profitsharing->workedDays		= empty($e['dias_trabajados']) ? null : $e['dias_trabajados'];
								$t_profitsharing->totalSalary		= empty($e['sueldo_total']) ? null : $e['sueldo_total'];
								$t_profitsharing->ptuForDays		= empty($e['ptu_por_dias']) ? null : $e['ptu_por_dias'];
								$t_profitsharing->ptuForSalary		= empty($e['ptu_por_sueldos']) ? null : $e['ptu_por_sueldos'];
								$t_profitsharing->totalPtu			= empty($e['ptu_total']) ? null : $e['ptu_total'];
								$t_profitsharing->exemptPtu			= empty($e['ptu_exenta']) ? null : $e['ptu_exenta'];
								$t_profitsharing->taxedPtu			= empty($e['ptu_gravada']) ? null : $e['ptu_gravada'];
								$t_profitsharing->totalPerceptions	= empty($e['total_de_percepciones']) ? null : $e['total_de_percepciones'];
								$t_profitsharing->isrRetentions		= empty($e['isr']) ? null : $e['isr'];
								$t_profitsharing->alimony			= empty($e['pension_alimenticia']) ? null : $e['pension_alimenticia'];
								$t_profitsharing->totalRetentions	= empty($e['total_de_retenciones']) ? null : $e['total_de_retenciones'];
								$t_profitsharing->netIncome			= empty($e['sueldo_neto']) ? null : $e['sueldo_neto'];
								$t_profitsharing->save();

								$sumatoria += $t_profitsharing->netIncome;
							}
						}
						else
						{
							$errors++;
						}
					}

					$nomina			= App\Nomina::find($request->idnomina);
					if ($sumatoria > 0) 
					{
						$nomina->amount	= $sumatoria;
					}
					$nomina->save();

					if ($errors > 0) 
					{
						$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
					}
					else
					{
						$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
					}
				}
				else
				{
					$alert	= "swal('','".lang::get("messages.separator_error")."', 'error');";
				}
				return back()->with('alert',$alert);
			}
			else
			{
				$alert	= "swal('', '".Lang::get("messages.extension_allowed",["param" => 'CSV'])."', 'error');";
				return back()->with('alert',$alert);
			}
		}
	}

	public function complementUpdate(Request $request)
	{
		if(Auth::user()->module->whereIn('id',[168,169,170])->count()>0)
		{
			if($request->file('csv_file')->isValid() && $request->file('csv_file')->getClientOriginalExtension() == "csv")
			{
				$delimiters = ["," => 0, ";" => 0];
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
					$name		= '/update_complement/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
					\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file')),'UTF-8'));
					$path		= \Storage::disk('reserved')->path($name);
					$csvArr		= array();
					if (($handle = fopen($path, "r")) !== FALSE)
					{
						$count = 0;
						while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
						{
							if($count > 1)
							{
								$csvArr[]	= $data;
							}
							$count++;
						}
						fclose($handle);
					}
					array_walk($csvArr, function(&$a) use ($csvArr)
					{
						$a = array_combine($csvArr[0], $a);
					});
					array_shift($csvArr);
					$sumatoria	= 0;
					$errors		= 0;
					foreach ($csvArr as $key => $e)
					{
						if (isset($e['sueldo_neto_no_fiscal']) && isset($e['neto_total'])) 
						{
							$nominaEmp				= App\NominaEmployee::where('idnomina',$request->idnomina)->where('idrealEmployee',$e['id'])->first();
							$workingData			= App\WorkerData::with('places')->find($nominaEmp->idworkingData);
							$new_data				= $workingData->replicate();
							$new_data->complement	= empty($e['sueldo_neto_no_fiscal']) ? null : $e['sueldo_neto_no_fiscal'];
							$new_data->visible		= 0;
							$new_data->push();
							$nominaEmp->idworkingData = $new_data->id;

							$nominaEmpNF					= App\NominaEmployeeNF::where('idnominaEmployee',$nominaEmp->idnominaEmployee)->first();
							$nominaEmpNF->extra_time		= empty($e['total_horas_extra']) ? null : $e['total_horas_extra'];
							$nominaEmpNF->holiday			= empty($e['total_dias_festivos']) ? null : $e['total_dias_festivos'];
							$nominaEmpNF->sundays			= empty($e['total_domingos_trabajados']) ? null : $e['total_domingos_trabajados'];
							$nominaEmpNF->complementPartial	= empty($e['sueldo_neto_no_fiscal']) ? null : $e['sueldo_neto_no_fiscal'];
							$nominaEmpNF->amount			= empty($e['total_no_fiscal_por_pagar']) ? null : $e['total_no_fiscal_por_pagar'];

							$nominaEmpNF->amount			= empty($e['total_no_fiscal_por_pagar']) ? null : $e['total_no_fiscal_por_pagar'];
							$nominaEmpNF->netIncome			= empty($e['neto_total']) ? null : $e['neto_total'];
							$nominaEmpNF->save();
							$nominaEmp->save();

							App\DiscountsNomina::where('idnominaemployeenf',$nominaEmp->idnominaemployeenf)->delete();

							if ($data['retencion_infonavit'] > 0) 
							{
								$t_discount                     = new App\DiscountsNomina();
								$t_discount->amount             = $data['retencion_infonavit'];
								$t_discount->reason             = 'INFONAVIT parte fiscal';
								$t_discount->idnominaemployeenf = $nominaEmpNF->idnominaemployeenf;
								$t_discount->save();
							}

							$sumatoria += $nominaEmpNF->amount;
						}
						else
						{
							$errors++;
						}
					}

					$nomina			= App\Nomina::find($request->idnomina);
					if ($sumatoria > 0) 
					{
						$nomina->amount	= $sumatoria;
					}
					$nomina->save();

					if ($errors > 0) 
					{
						$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
					}
					else
					{
						$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
					}
				}
				else
				{
					$alert	= "swal('','".lang::get("messages.separator_error")."', 'error');";
				}
				return back()->with('alert',$alert);
			}
			else
			{
				$alert	= "swal('', '".Lang::get("messages.extension_allowed",["param" => 'CSV'])."', 'error');";
				return back()->with('alert',$alert);
			}

		}
		else
		{
			return redirect('/');
		}
	}

	public function complementUpdateConstruction(Request $request)
	{
		if(Auth::user()->module->whereIn('id',[168,169,170])->count()>0)
		{
			if($request->file('csv_file')->isValid() && $request->file('csv_file')->getClientOriginalExtension() == "csv")
			{
				$delimiters = ["," => 0, ";" => 0];
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
					$name		= '/update_complement/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
					\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file')),'UTF-8'));
					$path		= \Storage::disk('reserved')->path($name);
					$csvArr		= array();
					if (($handle = fopen($path, "r")) !== FALSE)
					{
						$count = 0;
						while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
						{
							if($count > 1)
							{
								$csvArr[]	= $data;
							}
							$count++;
						}
						fclose($handle);
					}
					array_walk($csvArr, function(&$a) use ($csvArr)
					{
						$a = array_combine($csvArr[0], $a);
					});
					array_shift($csvArr);
					$sumatoria	= 0;
					$errors		= 0;

					$folio 					= App\Nomina::find($request->idnomina)->idFolio;
					$req_no_fiscal 			= App\RequestModel::find($folio);
					$check_request_fiscal 	= App\RequestModel::where('kind',16)
												->where('idprenomina',$req_no_fiscal->idprenomina)
												->where('idDepartment',$req_no_fiscal->idDepartment)
												->where('taxPayment',1)
												->first();

					$flagSalary = false;

					if (App\Nomina::find($request->idnomina)->idCatTypePayroll == "001") 
					{
						$flagSalary = true;
					}
					else
					{
						$flagSalary = false;
					}
				
					if ($flagSalary) 
					{
						$headers = 
						[
							'id',
							'empleado',
							'curp',
							'proyecto', 
							'empresa', 
							'sdi', 
							'periodicidad', 
							'sueldo_neto',
							'complemento',  
							'forma_de_pago',
							'alias', 
							'banco',  
							'clabe',  
							'cuenta',  
							'tarjeta',  
							'sucursal',
							'dias_trabajados',
							'faltas',
							'horas_extra',
							'dias_festivos',
							'domingos_trabajados',
							'referencia',
							'razon_de_pago', 
							'descuento', 
							'extra', 
							'infonavit_fiscal',
							'fonacot_fiscal',
							'prestamo_fiscal',
							'sueldo_neto_fiscal',
							'retencion_infonavit',
							'retencion_fonacot',
							'total_horas_extra',
							'total_dias_festivos',
							'total_domingos_trabajados',
							'sueldo_neto_no_fiscal', 
							'total_no_fiscal_por_pagar',
							'neto_total'
						];
					}
					else
					{
						$headers = 
						[
							'id',
							'empleado',
							'curp',
							'proyecto', 
							'empresa', 
							'sdi', 
							'periodicidad', 
							'sueldo_neto',
							'complemento', 
							'forma_de_pago',
							'alias',
							'banco', 
							'clabe', 
							'cuenta', 
							'tarjeta', 
							'sucursal', 
							'referencia',
							'razon_de_pago',
							'total_fiscal_pagado',
							'descuento', 
							'extra', 
							'total_no_fiscal_por_pagar',
							'neto_total'
						];
					}

					if(empty($csvArr) || array_diff($headers, array_keys($csvArr[0])))
					{
						$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
						return back()->with('alert',$alert);	
					}

					foreach ($csvArr as $key => $e)
					{
						if (isset($e['total_no_fiscal_por_pagar']) && isset($e['neto_total'])) 
						{
							$nominaEmp	= App\NominaEmployee::where('idnomina',$request->idnomina)->where('idrealEmployee',$e['id'])->first();

							if ($nominaEmp != "") 
							{
								if ($req_no_fiscal->nominasReal->first()->idCatTypePayroll == '001') 
								{
									if ($check_request_fiscal == "") 
									{
										$nominaEmp->absence		= empty($e['faltas']) ? "0" : ($e['faltas'] < 0 ? '0' : $e['faltas']);
										$nominaEmp->extra_hours	= empty($e['horas_extra']) ? "0" : ($e['horas_extra'] < 0 ? '0' : $e['horas_extra']);
										$nominaEmp->holidays	= empty($e['dias_festivos']) ? "0" : ($e['dias_festivos'] < 0 ? '0' : $e['dias_festivos']);
										$nominaEmp->sundays		= empty($e['domingos_trabajados']) ? "0" : ($e['domingos_trabajados'] < 0 ? '0' : $e['domingos_trabajados']);
									}
								}

								$workingData			= App\WorkerData::with('places')->find($nominaEmp->idworkingData);
								$new_data				= $workingData->replicate();
								$new_data->complement	= empty($e['sueldo_neto_no_fiscal']) ? '0' : ($e['sueldo_neto_no_fiscal'] < 0 ? '0' : $e['sueldo_neto_no_fiscal']);
								$new_data->visible		= 0;
								$new_data->push();
								$nominaEmp->idworkingData = $new_data->id;

								$nominaEmpNF	= App\NominaEmployeeNF::where('idnominaEmployee',$nominaEmp->idnominaEmployee)->first();
								
								if ($req_no_fiscal->nominasReal->first()->idCatTypePayroll == '001') 
								{
									$nominaEmpNF->extra_time		= empty($e['total_horas_extra']) ? '0' : ($e['total_horas_extra'] < 0 ? '0' : $e['total_horas_extra']);
									$nominaEmpNF->holiday			= empty($e['total_dias_festivos']) ? '0' : ($e['total_dias_festivos'] < 0 ? '0' : $e['total_dias_festivos']);
									$nominaEmpNF->sundays			= empty($e['total_domingos_trabajados']) ? '0' : ($e['total_domingos_trabajados'] < 0 ? '0' : $e['total_domingos_trabajados']);
									$nominaEmpNF->complementPartial	= empty($e['sueldo_neto_no_fiscal']) ? '0' : ($e['sueldo_neto_no_fiscal'] < 0 ? '0' : $e['sueldo_neto_no_fiscal']);
									$nominaEmpNF->amount			= empty($e['total_no_fiscal_por_pagar']) ? '0' : ($e['total_no_fiscal_por_pagar'] < 0 ? '0' : $e['total_no_fiscal_por_pagar']);
								}

								$nominaEmpNF->amount	= empty($e['total_no_fiscal_por_pagar']) ? '0' : ($e['total_no_fiscal_por_pagar'] < 0 ? '0' : $e['total_no_fiscal_por_pagar']);
								$nominaEmpNF->netIncome	= empty($e['neto_total']) ? '0' : ($e['neto_total'] < 0 ? '0' : $e['neto_total']);
								$nominaEmpNF->save();
								$nominaEmp->save();

								App\DiscountsNomina::where('idnominaemployeenf',$nominaEmpNF->idnominaemployeenf)->where('reason','like','%INFONAVIT%')->delete();

								if (!empty($e['retencion_infonavit']) && $e['retencion_infonavit'] > 0) 
								{
									$t_discount                     = new App\DiscountsNomina();
									$t_discount->amount             = $e['retencion_infonavit'];
									$t_discount->reason             = 'INFONAVIT parte fiscal';
									$t_discount->idnominaemployeenf = $nominaEmpNF->idnominaemployeenf;
									$t_discount->save();
								}
								$sumatoria += $nominaEmpNF->amount;
							}
						}
						else
						{
							$errors++;
						}
					}
					$nomina			= App\Nomina::find($request->idnomina);
					if ($sumatoria > 0) 
					{
						$nomina->amount	= $sumatoria;
					}
					$nomina->save();
					if ($errors > 0) 
					{
						$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
					}
					else
					{
						$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
					}
				}
				else
				{
					$alert	= "swal('','".lang::get("messages.separator_error")."', 'error');";
				}
				return back()->with('alert',$alert);
			}
			else
			{
				$alert	= "swal('', '".Lang::get("messages.extension_allowed",["param" => 'CSV'])."', 'error');";
				return back()->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function exportNominaEmployee($id)
	{
			$selectRaw 		= '
								real_employees.last_name as last_name,
								real_employees.scnd_last_name as scnd_last_name,
								real_employees.name as name,
								real_employees.curp as curp,
								real_employees.rfc as rfc,
								real_employees.imss as imss,
								real_employees.street as street,
								real_employees.number as number,
								real_employees.colony as colony,
								real_employees.cp as cp,
								real_employees.city as city,
								employee_states.description as employee_state,
								worker_states.description as worker_state,
								projects.proyectName as project_name,
								enterprises.name as enterprise_name,
								CONCAT_WS(" - ",accounts.account, accounts.description) as account,
								places.places as places,
								areas.name as direction_name,
								departments.name as department_name,
								worker_datas.position as position,
								DATE_FORMAT(worker_datas.admissionDate, "%d-%m-%Y") as admissionDate,
								DATE_FORMAT(worker_datas.imssDate, "%d-%m-%Y") as imssDate,
								DATE_FORMAT(worker_datas.downDate, "%d-%m-%Y") as downDate,
								DATE_FORMAT(worker_datas.endingDate, "%d-%m-%Y") as endingDate,
								DATE_FORMAT(worker_datas.reentryDate, "%d-%m-%Y") as reentryDate,
								cat_contract_types.description as contract_type,
								worker_datas.regime_id as regime_id,
								IF(worker_datas.workerStatus = 1, "Activo",
									IF(worker_datas.workerStatus = 2, "Baja pacial",
										IF(worker_datas.workerStatus = 3, "Baja definitiva",
											IF(worker_datas.workerStatus = 4, "Suspensión",
												IF(worker_datas.workerStatus = 5, "Boletinado","")
											)
										)
									)
								) as worker_status,
								worker_datas.sdi as sdi,
								cat_periodicities.description as periodicity,
								worker_datas.employer_register as employer_register,
								payment_methods.method as payment_method,
								worker_datas.netIncome as netIncome,
								worker_datas.complement as complement,
								worker_datas.fonacot as fonacot,
								worker_datas.nomina as nomina,
								worker_datas.bono as bono,
								worker_datas.infonavitCredit as infonavitCredit,
								worker_datas.infonavitDiscount as infonavitDiscount,
								IF(worker_datas.infonavitDiscountType = 1,"VSM (Veces Salario Mínimo)",
									IF(worker_datas.infonavitDiscountType = 2,"Cuota fija",
										IF(worker_datas.infonavitDiscountType = 3,"Porcentaje","")
									)
								) as infonavit_discount_type,
								bank_data.alias as alias,
								cat_banks.description as bank,
								bank_data.clabe as clabe,
								bank_data.account as bank_account,
								bank_data.cardNumber as cardNumber,
								bank_data.branch as branch
							';
			$nominaEmployees = DB::table('request_models')
							->selectRaw($selectRaw)
							->leftJoin('nominas','nominas.idFolio','request_models.folio')
							->leftJoin('nomina_employees','nomina_employees.idnomina','nominas.idnomina')
							->leftJoin('real_employees','real_employees.id','nomina_employees.idrealEmployee')
							->leftJoin('states as employee_states','employee_states.idstate','real_employees.state_id')
							->leftJoin('worker_datas','worker_datas.id','nomina_employees.idworkingData')
							->leftJoin('states as worker_states','worker_states.idstate','worker_datas.state')
							->leftJoin('enterprises','enterprises.id','worker_datas.enterprise')
							->leftJoin('projects','projects.idproyect','worker_datas.project')
							->leftJoin('accounts','accounts.idAccAcc','worker_datas.account')
							->leftJoin(DB::raw('(SELECT idWorkingData, GROUP_CONCAT(place SEPARATOR ", ") as places FROM worker_data_places INNER JOIN places ON worker_data_places.idPlace=places.id GROUP BY idWorkingData) as places'),'worker_datas.id','places.idWorkingData')
							->leftJoin('areas','areas.id','worker_datas.direction')
							->leftJoin('departments','departments.id','worker_datas.department')
							->leftJoin('cat_contract_types','cat_contract_types.id','worker_datas.workerType')
							->leftJoin('cat_periodicities','cat_periodicities.c_periodicity','worker_datas.periodicity')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','worker_datas.paymentWay')
							->leftJoin(DB::raw('(SELECT * FROM employee_accounts WHERE id IN(SELECT MIN(id) as id FROM employee_accounts WHERE visible = 1 GROUP BY idEmployee)) as bank_data'),'real_employees.id','bank_data.idEmployee')
							->leftJoin('cat_banks','bank_data.idCatBank','cat_banks.c_bank')
							->where('request_models.folio',$id)
							->where('nomina_employees.visible',1)
							->orderBy('real_employees.last_name','ASC')
							->orderBy('real_employees.scnd_last_name','ASC')
							->orderBy('real_employees.name','ASC')
							->get();

			$request_model = App\RequestModel::find($id);

			$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat	= (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setFontBold()->build();
			$writer			= WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Plantilla -'.$request_model->nominasReal->first()->title.'.xlsx');
			$writer->getCurrentSheet()->setName('Empleados');
			$headerArray	= ['Reporte de Empleados','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
			$tempHeaders		= [];
			foreach($headerArray as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$headerArray	= ['Apellido','Apellido Materno','Nombre','CURP','RFC','IMSS','Calle','Número','Colonia','CP','Ciudad','Estado','Estado laboral','Contrato','Empresa','Clasificación de gasto','Lugar de trabajo','Dirección','Departamento','Puesto','Fecha de ingreso','Fecha de alta','Fecha de baja','Fecha der termino','Fecha de reingreso','Tipo contrato','Regimen','Estatus','SDI','Periodicidad','Registro patronal','Forma pago','Sueldo neto','Complemento','Fonacot','Porcentaje nomina','Porcentaje bono','Crédito infonavit','Descuento infonavit','Tipo de descuento infonavit','Alias','Banco','Clabe','Cuenta','Tarjeta','Sucursal'];
			$tempHeaders		= [];
			foreach($headerArray as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$tempCurp	= '';
			$kindRow			= true;
			$flagAlimony 		= false;
			foreach($nominaEmployees as $nomina_employee)
			{
				if($tempCurp != $nomina_employee->curp)
				{
					$tempCurp = $nomina_employee->curp;
					$kindRow = !$kindRow;
					
				}
				$tmpArr = [];
				foreach($nomina_employee as $k => $n)
				{
					$tmpArr[] = WriterEntityFactory::createCell($n);
				}
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr);
				}
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
	}

	public function deleteEmployee(Request $request,$id)
	{
		if(Auth::user()->module->whereIn('id',[168,169,170])->count()>0)
		{
			$nominaEmployee 	= App\NominaEmployee::find($id);
			$nomina				= App\Nomina::find($nominaEmployee->idnomina);
			$folio				= $nomina->idFolio;
			$requestNomina		= App\RequestModel::find($folio);
			
			if ($requestNomina->taxPayment == 1) 
			{
				switch ($nomina->idCatTypePayroll) 
				{
					case '001':			
						$idSalary = App\Salary::where('idnominaEmployee',$id)->first()->idSalary;
						App\NominaEmployeeAccounts::where('idSalary',$idSalary)->delete();
						App\Salary::where('idnominaEmployee',$id)->delete();
						$nominaEmployee->delete();
						$totalRequest = 0;
						foreach ($nomina->nominaEmployee as $n) 
						{
							$totalRequest += $n->salary->first()->netIncome;
						}
						$nomina->amount	= $totalRequest;
						$nomina->save();
						$alert	= "swal('','".Lang::get("messages.record_deleted")."', 'success');";
						return back()->with('alert',$alert);
						break;

					case '002':			
						$idBonus = App\Bonus::where('idnominaEmployee',$id)->first()->idBonus;
						App\NominaEmployeeAccounts::where('idBonus',$idBonus)->delete();
						App\Bonus::where('idnominaEmployee',$id)->delete();
						$nominaEmployee->delete();
						$totalRequest = 0;
						foreach ($nomina->nominaEmployee as $n) 
						{
							$totalRequest += $n->bonus->first()->netIncome;
						}
						$nomina->amount	= $totalRequest;
						$nomina->save();
						$alert	= "swal('','".Lang::get("messages.record_deleted")."', 'success');";
						return back()->with('alert',$alert);
						break;

					case '003':			
					case '004':			
						$idLiquidation = App\Liquidation::where('idnominaEmployee',$id)->first()->idLiquidation;
						App\NominaEmployeeAccounts::where('idLiquidation',$idLiquidation)->delete();
						App\Liquidation::where('idnominaEmployee',$id)->delete();
						$nominaEmployee->delete();
						$totalRequest = 0;
						foreach ($nomina->nominaEmployee as $n) 
						{
							$totalRequest += $n->liquidation->first()->netIncome;
						}
						$nomina->amount = $totalRequest;
						$nomina->save();
						$alert	= "swal('','".Lang::get("messages.record_deleted")."', 'success');";
						return back()->with('alert',$alert);
						break;

					case '005':			
						$idvacationPremium = App\VacationPremium::where('idnominaEmployee',$id)->first()->idvacationPremium;
						App\NominaEmployeeAccounts::where('idvacationPremium',$idvacationPremium)->delete();
						App\VacationPremium::where('idnominaEmployee',$id)->delete();
						$nominaEmployee->delete();
						$totalRequest = 0;
						foreach ($nomina->nominaEmployee as $n) 
						{
							$totalRequest += $n->vacationPremium->first()->netIncome;
						}
						$nomina->amount	= $totalRequest;
						$nomina->save();
						$alert	= "swal('','".Lang::get("messages.record_deleted")."', 'success');";
						return back()->with('alert',$alert);
						break;

					case '006':			
						$idprofitSharing = App\ProfitSharing::where('idnominaEmployee',$id)->first()->idprofitSharing;
						App\NominaEmployeeAccounts::where('idprofitSharing',$idprofitSharing)->delete();
						App\ProfitSharing::where('idnominaEmployee',$id)->delete();
						$nominaEmployee->delete();
						$totalRequest = 0;
						foreach ($nomina->nominaEmployee as $n) 
						{
							$totalRequest += $n->profitSharing->first()->netIncome;
						}
						$nomina->amount	= $totalRequest;
						$nomina->save();
						$alert	= "swal('','".Lang::get("messages.record_deleted")."', 'success');";
						return back()->with('alert',$alert);
						break;

					default:
						# code...
						break;
				}
			}
			else
			{
				App\NominaEmployeeNF::where('idnominaEmployee',$id)->delete();
				$nominaEmployee->delete();
				$totalRequest = 0;
				foreach ($nomina->nominaEmployee as $n) 
				{
					$totalRequest += $n->nominasEmployeeNF->first()->amount;
				}
				$nomina->amount = $totalRequest;
				$nomina->save();
				$alert	= "swal('','".Lang::get("messages.record_deleted")."', 'success');";
				return back()->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function declineRequest(Request $request,$id, $submodule_id = "")
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$requestDecline			= App\RequestModel::find($id);

			if (in_array($requestDecline->status,[5,6,7,13,16]))
			{
				$alert	= "swal('','".Lang::get("messages.request_already_ruled")."', 'error');";
				return searchRedirect($submodule_id, $alert, 'administration/nomina');
			}
			

			$requestNF = App\RequestModel::where('idprenomina',$requestDecline->idprenomina)->where('taxPayment',0)->where('status',2)->first();
			if ($requestNF != null || $requestNF != '') 
			{
				$requestNF->status = $request->decline_status;
				$requestNF->save();	
			}

			$requestDecline->status	= $request->decline_status;
			$requestDecline->save();

			$alert	= "swal('','".Lang::get("messages.request_ruled")."', 'success');";
			return searchRedirect($submodule_id, $alert, 'administration/nomina');
		}
	}

	public function nominaPrecalculate(Request $request,$id)
	{
		if (Auth::user()->module->where('id',166)->count()>0) 
		{
			$data		= App\Module::find($this->module_id);
			$request	= App\RequestModel::find($id);

			if ($request != '') 
			{
				return view('administracion.nomina.precalculo-nomina',[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 166,
					'request'	=> $request
				]);
			}
			else
			{
				return redirect('error');
			}
		}
	}

	public function getNominaPrecalculate(Request $request,$id)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$t_request				= App\RequestModel::find($id);

			$checkFiscal = App\RequestModel::where('kind',16)
				->where('idprenomina',$t_request->idprenomina)
				->where('idDepartment',$t_request->idDepartment)
				->where('taxPayment',1)
				->whereNotIn('folio',[$id])
				->first();

			$flagCheckFiscal = true;

			if ($flagCheckFiscal) 
			{
				$flag = false;
				$request_type	= App\RequestModel::find($id);

				if ($request_type->taxPayment == 1) 
				{
					switch ($request->type_payroll) 
					{
						case '001':
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee		= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$admissionDate			= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$sdi					= $t_nominaemployee->workerData->first()->sdi;
								$primaDeRiesgoDeTrabajo	= isset(App\EmployerRegister::where('employer_register',$t_nominaemployee->workerData->first()->employer_register)->first()->risk_number) ? App\EmployerRegister::where('employer_register',$t_nominaemployee->workerData->first()->employer_register)->first()->risk_number : ''; 

								if ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null || $primaDeRiesgoDeTrabajo == '' || $primaDeRiesgoDeTrabajo == null) 
								{
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con los siguientes datos: Fecha de Alta, SDI y Registro patronal","error");';

									return back()->with('alert',$alert);
								}

								$calculations = [];
								$calculations['admissionDate']  = $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								if (new \DateTime($request->from_date[$i]) < new \DateTime($calculations['admissionDate'])) 
								{
									$datetime1 = date_create($request->from_date[$i]);
									$datetime2 = date_create($calculations['admissionDate']);
									$interval  = date_diff($datetime1, $datetime2);
									$daysStart = $interval->format('%a');
								}
								else
								{
									$daysStart = 0;
								}
								$downDate = $t_nominaemployee->workerData->first()->downDate != '' && new \DateTime($t_nominaemployee->workerData->first()->downDate) > new \DateTime($calculations['admissionDate']) ? $t_nominaemployee->workerData->first()->downDate : null;
								$daysDown = 0;
								if ($downDate !='' && new \DateTime($downDate) >= new \DateTime($request->from_date[$i]) && new \DateTime($downDate) <= new \DateTime($request->to_date[$i])) 
								{
									$date1    = new \DateTime($downDate);
									$date2    = new \DateTime($request->to_date[$i]);
									$diff     = $date1->diff($date2);
									$daysDown = $diff->days;
								}
								else
								{
									$daysDown = 0;
								}
								$calculations['workedDays']  = (App\CatPeriodicity::find($request->periodicity[$i])->days)-$request->absence[$i]-$daysStart-$daysDown;

								if ($calculations['workedDays'] < 1) 
								{
									$alert = 'swal("","Error, El empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' no puede tener 0 días trabajados. Por favor verifique los siguientes datos: Faltas, Fecha de Alta, Fecha de Inicial y Final de Pago.","error");';
									return back()->with('alert',$alert);
								}
							}
							break;

						case '002':
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee	= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$paymentWay			= $request->paymentWay[$i];
								$idemployeeAccount	= $request->idemployeeAccount[$i];
								//calculo para dias de vacaciones
								$admissionDate		= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$sdi			= $t_nominaemployee->workerData->first()->sdi;
								
								if ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null || $paymentWay == null || $paymentWay == '') 
								{
									
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con los siguientes datos: Fecha de Alta, SDI, Forma de pago","error");';

									return back()->with('alert',$alert);
								}
								if ($paymentWay == 1 && ($idemployeeAccount == '' || $idemployeeAccount== null)) 
								{
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con una cuenta bancaria o cambie la forma de pago a Efectivo","error");';

									return back()->with('alert',$alert);
								}
								if ($paymentWay == 2 && ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null))
								{
									
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con los siguientes datos: Fecha de Alta, SDI, Forma de pago","error");';

									return back()->with('alert',$alert);
								}

							}
							break;

						case '003':
						case '004':
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee	= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$paymentWay			= $request->paymentWay[$i];
								$idemployeeAccount	= $request->idemployeeAccount[$i];
								$admissionDate		= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$sdi				= $t_nominaemployee->workerData->first()->sdi;

								if ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null || $paymentWay == null || $paymentWay == '') 
								{
									
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con los siguientes datos: Fecha de Alta, SDI, Forma de pago","error");';

									return back()->with('alert',$alert);
								}
								if ($paymentWay == 1 && ($idemployeeAccount == '' || $idemployeeAccount== null)) 
								{
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con una cuenta bancaria o cambie la forma de pago a Efectivo","error");';

									return back()->with('alert',$alert);
								}
								if ($paymentWay == 2 && ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null))
								{
									
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con los siguientes datos: Fecha de Alta, SDI, Forma de pago","error");';

									return back()->with('alert',$alert);
								}
							}

							break;

						case '005':
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee	= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$admissionDate		= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$sdi				= $t_nominaemployee->workerData->first()->sdi;
								$paymentWay			= $request->paymentWay[$i];
								$idemployeeAccount	= $request->idemployeeAccount[$i];
								
								if ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null || $paymentWay == null || $paymentWay == '') 
								{
									
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con los siguientes datos: Fecha de Alta, SDI, Forma de pago","error");';

									return back()->with('alert',$alert);
								}
								if ($paymentWay == 1 && ($idemployeeAccount == '' || $idemployeeAccount== null)) 
								{
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con una cuenta bancaria o cambie la forma de pago a Efectivo","error");';

									return back()->with('alert',$alert);
								}
								if ($paymentWay == 2 && ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null))
								{
									
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con los siguientes datos: Fecha de Alta, SDI, Forma de pago","error");';

									return back()->with('alert',$alert);
								}
							}
							break;

						case '006':
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee	= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$paymentWay			= $request->paymentWay[$i];
								$idemployeeAccount	= $request->idemployeeAccount[$i];
								$admissionDate		= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$sdi				= $t_nominaemployee->workerData->first()->sdi;
								
								if ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null || $paymentWay == null || $paymentWay == '') 
								{
									
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con los siguientes datos: Fecha de Alta, SDI, Forma de pago","error");';

									return back()->with('alert',$alert);
								}
								if ($paymentWay == 1 && ($idemployeeAccount == '' || $idemployeeAccount== null)) 
								{
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con una cuenta bancaria o cambie la forma de pago a Efectivo","error");';

									return back()->with('alert',$alert);
								}
								if ($paymentWay == 2 && ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null))
								{
									
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con los siguientes datos: Fecha de Alta, SDI, Forma de pago","error");';

									return back()->with('alert',$alert);
								}

							}
							break;
					}
				}
				
				$t_request 		= App\RequestModel::find($id);
				$totalRequest 	= 0;

				$t_nomina = App\Nomina::find($t_request->nominasReal->first()->idnomina);

				if ($t_request->taxPayment == 1) 
				{
					switch ($request->type_payroll) 
					{
						case '001':
							$arrayNomina = [];
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee					= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);

								$calculations = [];
								//calculo para dias de vacaciones
									$calculations['admissionDate']	= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
									$calculations['nowDate']		= Carbon::now();
									$calculations['diasTrabajados'] = App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['admissionDate'],$calculations['nowDate']);
									$calculations['yearsWork']		= ceil($calculations['diasTrabajados']/365);
									if ($calculations['yearsWork'] > 24) 
									{
										$calculations['vacationDays']	= 20;
									}
									else
									{
										$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
									}

								//-------------------

								$calculations['prima_vac_esp']	= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
								$calculations['sdi']			= $t_nominaemployee->workerData->first()->sdi;
								$calculations['sd']				= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);

								$daysStart = 0;	
								if (new \DateTime($request->from_date[$i]) < new \DateTime($calculations['admissionDate'])) 
								{
									$datetime1	= date_create($request->from_date[$i]);
									$datetime2	= date_create($calculations['admissionDate']);
									$interval	= date_diff($datetime1, $datetime2);

									$daysStart = $interval->format('%a');

								}
								else
								{
									$daysStart = 0;
								}

								$downDate = $t_nominaemployee->workerData->first()->downDate != '' && new \DateTime($t_nominaemployee->workerData->first()->downDate) > new \DateTime($calculations['admissionDate']) ? $t_nominaemployee->workerData->first()->downDate : null;
								$daysDown = 0;
								if ($downDate !='' && new \DateTime($downDate) >= new \DateTime($request->from_date[$i]) && new \DateTime($downDate) <= new \DateTime($request->to_date[$i])) 
								{
									$date1		= new \DateTime($downDate);
									$date2		= new \DateTime($request->to_date[$i]);
									$diff		= $date1->diff($date2);
									$daysDown	= $diff->days;

								}
								else
								{
									$daysDown = 0;
								}

								$calculations['workedDays']		= (App\CatPeriodicity::find($request->periodicity[$i])->days)-$request->absence[$i]-$daysStart-$daysDown;

								$calculations['periodicity']	= App\CatPeriodicity::find($request->periodicity[$i])->description;
								$calculations['rangeDate']		= $request->from_date[$i].' '.$request->to_date[$i];

								switch ($request->periodicity[$i]) 
								{
									case '02':
										$d = new DateTime($request->from_date[$i]);
										$d->modify('next thursday');
										$calculations['divisorDayFormImss'] = App\Http\Controllers\AdministracionNominaController::days_count($d->format('m'),$d->format('Y'),4);
										break;

									case '04':
										$calculations['divisorDayFormImss'] = 2;
										break;

									case '05':
										$calculations['divisorDayFormImss'] = 1;
										break;
									
									default:
										break;
								}
								$d = new DateTime($request->from_date[$i]);
								$d->modify('next thursday');
								$calculations['daysMonth'] 		= App\Http\Controllers\AdministracionNominaController::days_month($d->format('m'),$d->format('Y'));

								if ($t_nominaemployee->WorkerData->first()->regime_id == '09') 
								{
									$calculations['daysForImss']	= 0;
								}
								else
								{
									switch ($request->periodicity[$i]) 
									{
										case '02':
											if ($calculations['workedDays']<7) 
											{
												$calculations['daysForImss']	= $calculations['workedDays'];
											}
											else
											{
												$calculations['daysForImss']	= $calculations['daysMonth']/$calculations['divisorDayFormImss'];
											}
											break;

										case '04':
											if ($calculations['workedDays']<15) 
											{
												$calculations['daysForImss']	= $calculations['workedDays'];
											}
											else
											{
												$calculations['daysForImss']	= $calculations['daysMonth']/$calculations['divisorDayFormImss'];
											}
											break;

										case '05':
											if ($calculations['workedDays']<30) 
											{
												$calculations['daysForImss']	= $calculations['workedDays'];
											}
											else
											{
												$calculations['daysForImss']	= $calculations['daysMonth']/$calculations['divisorDayFormImss'];
											}
											break;
										
										default:
											# code...
											break;
									}
								}
								
								
								//PERCEPCIONES
								$calculations['salary']			= $calculations['sd']*$calculations['workedDays'];
								$calculations['loanPerception']	= $request->loan_perception[$i];
								$calculations['puntuality']		= $calculations['salary'] * (($t_nominaemployee->workerData->first()->bono/100)/2);
								$calculations['assistance']		= $calculations['salary'] * (($t_nominaemployee->workerData->first()->bono/100)/2);

								//calculo para el subsidio

								$calculations['baseTotalDePercepciones']	= $calculations['salary'] + $calculations['puntuality'] + $calculations['assistance'];
								$calculations['baseISR']					= ($calculations['baseTotalDePercepciones']/$calculations['workedDays'])*30.4;
								
								$parameterISR								= App\ParameterISR::where('inferior','<=',$calculations['baseISR'])->where('lapse',30)->get();
								
								$calculations['limiteInferior']			= $parameterISR->last()->inferior;
								$calculations['excedente']				= $calculations['baseISR']-$calculations['limiteInferior'];
								$calculations['factor']					= $parameterISR->last()->excess/100;
								$calculations['isrMarginal']			= $calculations['excedente'] * $calculations['factor'];
								$calculations['cuotaFija']				= $parameterISR->last()->quota;
								$calculations['isrAntesDelSubsidio']	= (($calculations['isrMarginal'] + $calculations['cuotaFija'])/30.4)*$calculations['workedDays'];
								$parameterSubsidy						= App\ParameterSubsidy::where('inferior','<=',$calculations['baseISR'])->where('lapse',30)->get();

								if ($calculations['baseISR'] <= 7382.34) 
								{
									$calculations['subsidioAlEmpleo'] = ($parameterSubsidy->last()->subsidy/30.4)*$calculations['workedDays'];
								}
								else
								{
									$calculations['subsidioAlEmpleo'] = 0;
								}

								if (($calculations['isrAntesDelSubsidio'] - $calculations['subsidioAlEmpleo']) > 0) 
								{
									$calculations['isrARetener']	= $calculations['isrAntesDelSubsidio'] - $calculations['subsidioAlEmpleo'];
									$calculations['subsidio']		= 0; 	
								}
								else
								{
									$calculations['isrARetener']	= 0;
									$calculations['subsidio']		= round(($calculations['isrAntesDelSubsidio'] - $calculations['subsidioAlEmpleo'])*(-1),2); 	
								}

								$calculations['totalPerceptions']	= round($calculations['salary'],2) + round($calculations['loanPerception'],2) + round($calculations['puntuality'],2) + round($calculations['assistance'],2) + round($calculations['subsidio'],2);

								
								//----------------------------

								//RETENCIONES

								// calculo de IMSS (cuotas obrero-patronal)
								$calculations['SalarioBaseDeCotizacion']	= $calculations['sdi'];
								$calculations['diasDelPeriodoMensual']		= $calculations['daysForImss'];
								$calculations['diasDelPeriodoBimestral']	= $calculations['daysForImss'];
								$calculations['uma']						= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
								$calculations['primaDeRiesgoDeTrabajo']		= App\EmployerRegister::where('employer_register',$t_nominaemployee->workerData->first()->employer_register)->first()->risk_number; 
								
								if (($calculations['uma']*3) > $calculations['SalarioBaseDeCotizacion'])
								{
									$calculations['imssExcedente'] 			= 0;
								}
								else
								{
									$calculations['imssExcedente']			= ((($calculations['SalarioBaseDeCotizacion']-(3*$calculations['uma']))*$calculations['diasDelPeriodoMensual'])*0.4)/100;
								}
								$calculations['prestacionesEnDinero']		= (($calculations['SalarioBaseDeCotizacion']*$calculations['diasDelPeriodoMensual'])*0.25)/100;
								$calculations['gastosMedicosPensionados']	= (($calculations['SalarioBaseDeCotizacion']*$calculations['diasDelPeriodoMensual'])*0.375)/100;
								$calculations['invalidezVidaPatronal']		= (($calculations['SalarioBaseDeCotizacion']*$calculations['diasDelPeriodoMensual'])*0.625)/100;
								$calculations['cesantiaVejez']				= (($calculations['SalarioBaseDeCotizacion']*$calculations['diasDelPeriodoMensual'])*1.125)/100;

								$calculations['imss'] = $calculations['imssExcedente']+$calculations['prestacionesEnDinero']+$calculations['gastosMedicosPensionados']+$calculations['invalidezVidaPatronal']+$calculations['cesantiaVejez'];

								//calculo infonavit

								$calculations['diasBimestre']		= App\Http\Controllers\AdministracionNominaController::days_bimester($request->from_date[$i]);
								$calculations['factorInfonavit']	= App\Parameter::where('parameter_name','INFONAVIT_FACTOR')->first()->parameter_value;

								if ($t_nominaemployee->workerData->first()->infonavitDiscountType != '') 
								{
									$calculations['descuentoEmpleado']	= $t_nominaemployee->workerData->first()->infonavitDiscount;
									$calculations['quinceBimestral']	= App\Http\Controllers\AdministracionNominaController::pay_infonavit($request->from_date[$i],$request->to_date[$i]);
									switch ($t_nominaemployee->workerData->first()->infonavitDiscountType) 
									{
										case 1:
											$calculations['descuentoInfonavitTemp'] = (($calculations['descuentoEmpleado']*$calculations['factorInfonavit']*2)/$calculations['diasBimestre'])*$calculations['daysForImss']+$calculations['quinceBimestral']; 
											break;

										case 2:
											$calculations['descuentoInfonavitTemp'] = $calculations['descuentoEmpleado']*2/$calculations['diasBimestre']*$calculations['daysForImss']+$calculations['quinceBimestral']; 
											break;

										case 3:
											$calculations['descuentoInfonavitTemp'] = (($calculations['sdi']*($calculations['descuentoEmpleado']/100)*$calculations['daysForImss']))+$calculations['quinceBimestral'];
											break;

										default:
											# code...
											break;
									}
								}
								else
								{
									$calculations['descuentoInfonavitTemp'] = 0 ;
								}

								// -------------------

								$calculations['fonacot']			=(($t_nominaemployee->workerData->first()->fonacot/30.4)*$calculations['daysForImss']);
								$calculations['loanRetention']		= $request->loan_retention[$i];
								$calculations['otherRetentionConcept']	= $request->other_retention_concept[$i];
								$calculations['otherRetentionAmount']	= $request->other_retention_amount[$i];

								$calculations['totalRetentionsTemp']	= round($calculations['imss'],2)+round($calculations['descuentoInfonavitTemp'],2)+round($calculations['fonacot'],2)+round($calculations['loanRetention'],2)+round($calculations['isrARetener'],2)+round($calculations['otherRetentionAmount'],2);

								$calculations['percentage']  	= ($calculations['totalRetentionsTemp'] * 100) / $calculations['salary'];

								if ($calculations['percentage']>80) 
								{
									$calculations['descuentoInfonavit']		= 0 ;
									$calculations['descuentoInfonavitComplemento'] = $calculations['descuentoInfonavitTemp'];

								}
								else
								{
									$calculations['descuentoInfonavit']		= $calculations['descuentoInfonavitTemp'];
									$calculations['descuentoInfonavitComplemento'] = 0;
								}

								$calculations['totalRetentions']	= round($calculations['imss'],2)+round($calculations['descuentoInfonavit'],2)+round($calculations['fonacot'],2)+round($calculations['loanRetention'],2)+round($calculations['isrARetener'],2)+round($calculations['otherRetentionAmount'],2);
								
								$calculations['netIncome']			= $calculations['totalPerceptions']-$calculations['totalRetentions'];

								//return $calculations;
								$arrayNomina[$i]['curp']				= $t_nominaemployee->employee->first()->curp;
								$arrayNomina[$i]['empresa']				= $t_nominaemployee->workerData->first()->enterprises->name;
								$arrayNomina[$i]['proyecto']			= $t_nominaemployee->workerData->first()->projects()->exists() ? $t_nominaemployee->workerData->first()->projects->proyectName : "Sin Proyecto";
								$arrayNomina[$i]['nombre']				= $t_nominaemployee->employee->first()->name;
								$arrayNomina[$i]['apellido_paterno']	= $t_nominaemployee->employee->first()->last_name;
								$arrayNomina[$i]['apellido_materno']	= $t_nominaemployee->employee->first()->scnd_last_name;
								$arrayNomina[$i]['monto_total']			= round($request->netIncome[$i],2);
								$arrayNomina[$i]['monto_fiscal']		= round($calculations['netIncome'],2);
								$arrayNomina[$i]['complemento']			= round($request->netIncome[$i]-$calculations['netIncome']-$calculations['descuentoInfonavitComplemento'],2);

								$calculations	= [];
							}
							break;

						case '002':
							$arrayNomina = [];
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee				= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);

								$calculations = [];
								//calculo para dias de vacaciones
								$calculations['fechaIngreso']		= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$calculations['fechaActual']		= Carbon::now();
								$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
								$calculations['yearsWork']			= ceil($calculations['diasTrabajados']/365);
								if ($calculations['yearsWork'] > 24) 
								{
									$calculations['vacationDays']	= 20;
								}
								else
								{
									$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
								}

								//-------------------

								$calculations['prima_vac_esp']	= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
								$calculations['sdi']			= $t_nominaemployee->workerData->first()->sdi;
								$calculations['sd']				= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);
								$calculations['uma']							= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
								$calculations['exento']							= $calculations['uma']*30; 
								$calculations['diasParaAguinaldo']				= $request->day_bonus[$i];
								$calculations['parteProporcionalParaAguinaldo']	= round((15*$calculations['diasParaAguinaldo'])/365,6);


								// PERCEPCIONES AGUINALDO---------------------------------------------------------------------

								if (($calculations['parteProporcionalParaAguinaldo'] * $calculations['sd']) < $calculations['exento']) 
								{
									$calculations['aguinaldoExento'] = $calculations['parteProporcionalParaAguinaldo'] * $calculations['sd'];
								}
								else
								{
									$calculations['aguinaldoExento'] = $calculations['exento'];
								}

								if (($calculations['parteProporcionalParaAguinaldo'] * $calculations['sd']) > $calculations['exento']) 
								{
									$calculations['aguinaldoGravable'] = ($calculations['parteProporcionalParaAguinaldo'] * $calculations['sd'])-$calculations['aguinaldoExento'];
								}
								else
								{
									$calculations['aguinaldoGravable'] = 0;
								}

								$calculations['totalPercepciones'] = round($calculations['aguinaldoExento'],2) + round($calculations['aguinaldoGravable'],2);

								// --------------------------------------------------------------------------------------------

								// RETENCIONES- ISR ---------------------------------------------------------------------

								// ISR 1ER FRACCION

								$calculations['baseISR_fraccion1']			= round((($calculations['aguinaldoGravable']/365)*30.4)+($calculations['sd']*30),6);
								$parameterISRF1								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion1'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion1']	= $parameterISRF1->last()->inferior;
								$calculations['excedente_fraccion1']		= round($calculations['baseISR_fraccion1']-$calculations['limiteInferior_fraccion1'],6);
								$calculations['factor_fraccion1']			= round($parameterISRF1->last()->excess/100,6);
								$calculations['isrMarginal_fraccion1']		= round($calculations['excedente_fraccion1'] * $calculations['factor_fraccion1'],6);
								$calculations['cuotaFija_fraccion1']		= round($parameterISRF1->last()->quota,6);
								$calculations['isr_fraccion1']				= round($calculations['isrMarginal_fraccion1']+$calculations['cuotaFija_fraccion1'],6);

								// ISR 2DA FRACCION

								$calculations['baseISR_fraccion2']			= round($calculations['sd']*30,6);
								$parameterISRF2								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion2'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion2']	= $parameterISRF2->last()->inferior;
								$calculations['excedente_fraccion2']		= round($calculations['baseISR_fraccion2']-$calculations['limiteInferior_fraccion2'],6);
								$calculations['factor_fraccion2']			= round($parameterISRF2->last()->excess/100,6);
								$calculations['isrMarginal_fraccion2']		= round($calculations['excedente_fraccion2'] * $calculations['factor_fraccion2'],6);
								$calculations['cuotaFija_fraccion2']		= round($parameterISRF2->last()->quota,6);
								$calculations['isr_fraccion2']				= round($calculations['isrMarginal_fraccion2']+$calculations['cuotaFija_fraccion2'],6);

								$calculations['resta']		= round($calculations['isr_fraccion1']-$calculations['isr_fraccion2'],6);
								$calculations['factor1']	= round((($calculations['aguinaldoGravable']/365) * 30.4),6);
								if($calculations['factor1'] == 0)
								{
									$calculations['factor2']	= 0;
								}
								else
								{
									$calculations['factor2']	= round($calculations['resta']/$calculations['factor1'],6);
								}
								$calculations['isr']		= round($calculations['factor2']*$calculations['aguinaldoGravable'],6);

								$calculations['totalRetenciones'] = round($calculations['isr'],2);

								// --------------------------------------------------------------------------------------------

								
								$calculations['netIncome']			= $calculations['totalPercepciones']-$calculations['totalRetenciones'];

								$arrayNomina[$i]['curp']				= $t_nominaemployee->employee->first()->curp;
								$arrayNomina[$i]['empresa']			= $t_nominaemployee->workerData->first()->enterprises->name;
								$arrayNomina[$i]['proyecto']			= $t_nominaemployee->workerData->first()->projects()->exists() ? $t_nominaemployee->workerData->first()->projects->proyectName : "Sin Proyecto";
								$arrayNomina[$i]['nombre']			= $t_nominaemployee->employee->first()->name;
								$arrayNomina[$i]['apellido_paterno']	= $t_nominaemployee->employee->first()->last_name;
								$arrayNomina[$i]['apellido_materno']	= $t_nominaemployee->employee->first()->scnd_last_name;
								$arrayNomina[$i]['monto_total']		= round($request->netIncome[$i],2);
								$arrayNomina[$i]['monto_fiscal']		= round($calculations['netIncome'],2);
								$arrayNomina[$i]['complemento']		= round($request->netIncome[$i]-$calculations['netIncome'],2);

								$calculations = [];

							}
							
							break;

						case '003':
						case '004':
							$arrayNomina = [];
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee					= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);

								// ----- calculo para dias de vacaciones ---------------------------
								$calculations					= [];

								$calculations['fechaIngreso']	= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$calculations['fechaBaja']		= $request->down_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->down_date[$i])->format('Y-m-d') : null;
								
								$calculations['fechaActual']	= Carbon::now();
								$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
								$calculations['añosTrabajados']	= ceil($calculations['diasTrabajados']/365);

								$calculations['diasTrabajadosParaAñosCompletos'] = App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaBaja']);

								$calculations['añosCompletos']	= floor($calculations['diasTrabajadosParaAñosCompletos']/365);
								if ($calculations['añosTrabajados'] > 24) 
								{
									$calculations['diasDeVacaciones']	= 20;
								}
								else
								{
									$calculations['diasDeVacaciones']	= App\ParameterVacation::where('fromYear','<=',$calculations['añosTrabajados'])->where('toYear','>=',$calculations['añosTrabajados'])->first()->days;
								}

								//------------------------------------------------------------------
								
								$calculations['prima_vac_esp']		= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
								$calculations['sdi']				= $t_nominaemployee->workerData->first()->sdi;
								$calculations['sd']					= round($calculations['sdi']/((($calculations['diasDeVacaciones']*$calculations['prima_vac_esp'])+15+365)/365),2);
								
								$calculations['diasTrabajadosM']	= $request->worked_days[$i];
								
								$calculations['diasParaVacaciones']	= ($calculations['diasDeVacaciones']*$calculations['diasTrabajadosM'])/365;
								//dias trabajados para aguinaldo va del 1 de enero a la fecha de baja
								$date1 = new \DateTime(date("Y").'-01-01');
								$date2 = new \DateTime($calculations['fechaIngreso']);
								if ($date2 > $date1) 
								{
									$fechaParaDiasAguinaldo = $calculations['fechaIngreso'];
								}
								else
								{
									$fechaParaDiasAguinaldo = date("Y").'-01-01';
								}
								$calculations['diasTrabajadosParaAguinaldo'] = App\Http\Controllers\AdministracionNominaController::daysPassed($fechaParaDiasAguinaldo,$calculations['fechaBaja'])+1;

								$calculations['diasParaAguinaldo'] 	= ($calculations['diasTrabajadosParaAguinaldo']*15)/365;

								if ($request->type_payroll == '004') 
								{
									$calculations['sueldoPorLiquidacion']		= round($calculations['sd']*90,6);
									$calculations['veinteDiasPorAñoServicio']	= round(20*$calculations['añosCompletos']*$calculations['sd'],6);
									
									// VARIABLES -------------------------------------------------------
									$calculations['uma']							= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
									$calculations['salarioMinimo']					= App\Parameter::where('parameter_name','SALARY_VDF')->first()->parameter_value; 
									$calculations['valorPrimaAntiguedad']			= $calculations['salarioMinimo']*2;
									$calculations['exento']							= $calculations['uma']*90; 
									$calculations['valorAguinaldoExento']			= $calculations['uma']*30; 
									$calculations['valorPrimaVacacaionalExenta']	= $calculations['uma']*15; 
									$calculations['valorIndemnizacionExenta']		= $calculations['uma']*90;
									//  PRIMA DE ANTIGUEDAD ------------------------------------------------------------------

									if ($calculations['sd']>=$calculations['valorPrimaAntiguedad']) 
									{
										$calculations['primaAntiguedad'] = round($calculations['añosCompletos']*12*$calculations['valorPrimaAntiguedad'],6);
									}
									else
									{
										$calculations['primaAntiguedad'] = round($calculations['añosCompletos']*12*$calculations['sd'],6);
									}

									//  INDEMNIZACION ------------------------------------------------------------------
									$calculations['indemnizacion'] =  round($calculations['sueldoPorLiquidacion']+$calculations['veinteDiasPorAñoServicio']+$calculations['primaAntiguedad'],6);

									if ($calculations['indemnizacion'] < $calculations['valorIndemnizacionExenta']) 
									{
										$calculations['indemnizacionExcenta']	= $calculations['indemnizacion'];
									}
									else
									{
										$calculations['indemnizacionExcenta']	= $calculations['valorIndemnizacionExenta'];
									}


									if ($calculations['indemnizacion'] > $calculations['valorIndemnizacionExenta']) 
									{
										$calculations['indemnizacionGravada']	= $calculations['indemnizacion']-$calculations['indemnizacionExcenta'];
									}
									else
									{
										$calculations['indemnizacionGravada']	= 0;
									}

									$calculations['vacaciones']				= $calculations['diasParaVacaciones']*$calculations['sd'];


									// PERCEPCIONES AGUINALDO---------------------------------------------------------------------

									if (($calculations['diasParaAguinaldo'] * $calculations['sd']) < $calculations['valorAguinaldoExento']) 
									{
										$calculations['aguinaldoExento'] = $calculations['diasParaAguinaldo'] * $calculations['sd'];
									}
									else
									{
										$calculations['aguinaldoExento'] = $calculations['valorAguinaldoExento'];
									}

									if (($calculations['diasParaAguinaldo'] * $calculations['sd']) > $calculations['valorAguinaldoExento']) 
									{
										$calculations['aguinaldoGravable'] = ($calculations['diasParaAguinaldo'] * $calculations['sd'])-$calculations['aguinaldoExento'];
									}
									else
									{
										$calculations['aguinaldoGravable'] = 0;
									}


									//-------- PERCEPCIONES ---------------------------------------------------------------


									if (($calculations['vacaciones']*$calculations['prima_vac_esp'])<$calculations['valorPrimaVacacaionalExenta'])
									{
										$calculations['primaVacacionalExenta'] = round($calculations['vacaciones']*$calculations['prima_vac_esp'],6);
									}
									else
									{
										$calculations['primaVacacionalExenta'] = $calculations['valorPrimaVacacaionalExenta'];
									}

									if (($calculations['vacaciones']*$calculations['prima_vac_esp'])>$calculations['valorPrimaVacacaionalExenta'])
									{
										$calculations['primaVacacionalGravada'] = round(($calculations['vacaciones']*$calculations['prima_vac_esp'])-$calculations['primaVacacionalExenta'],6);
									}
									else
									{
										$calculations['primaVacacionalGravada'] = 0;
									}

									$calculations['otrasPercepciones'] = $request->other_perception[$i];

									$calculations['totalPercepciones'] = round($calculations['sueldoPorLiquidacion'],2)+round($calculations['veinteDiasPorAñoServicio'],2)+round($calculations['primaAntiguedad'],2)+round($calculations['vacaciones'],2)+round($calculations['aguinaldoExento'],2)+round($calculations['aguinaldoGravable'],2)+round($calculations['primaVacacionalExenta'],2)+round($calculations['primaVacacionalGravada'],2)+round($calculations['otrasPercepciones'],2);
								}
								else
								{
									// VARIABLES -------------------------------------------------------
									$calculations['uma']							= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
									$calculations['salarioMinimo']					= App\Parameter::where('parameter_name','SALARY_VDF')->first()->parameter_value; 
									$calculations['valorPrimaAntiguedad']			= $calculations['salarioMinimo']*2;
									$calculations['exento']							= $calculations['uma']*90; 
									$calculations['valorAguinaldoExento']			= $calculations['uma']*30; 
									$calculations['valorPrimaVacacaionalExenta']	= $calculations['uma']*15; 
									$calculations['valorIndemnizacionExenta']		= $calculations['uma']*90;
									//  PRIMA DE ANTIGUEDAD ------------------------------------------------------------------

									if ($calculations['sd']>=$calculations['valorPrimaAntiguedad']) 
									{
										$calculations['primaAntiguedad'] = round($calculations['añosCompletos']*12*$calculations['valorPrimaAntiguedad'],6);
									}
									else
									{
										$calculations['primaAntiguedad'] = round($calculations['añosCompletos']*12*$calculations['sd'],6);
									}

									//  INDEMNIZACION  ------------------------------------------------------------------

									if ($calculations['primaAntiguedad'] < $calculations['valorIndemnizacionExenta']) 
									{
										$calculations['indemnizacionExcenta']	= $calculations['primaAntiguedad'];
									}
									else
									{
										$calculations['indemnizacionExcenta']	= $calculations['valorIndemnizacionExenta'];
									}


									if ($calculations['primaAntiguedad'] > $calculations['valorIndemnizacionExenta']) 
									{
										$calculations['indemnizacionGravada']	= $calculations['primaAntiguedad']-$calculations['indemnizacionExcenta'];
									}
									else
									{
										$calculations['indemnizacionGravada']	= 0;
									}

									$calculations['vacaciones']				= $calculations['diasParaVacaciones']*$calculations['sd'];


									// PERCEPCIONES AGUINALDO---------------------------------------------------------------------

									if (($calculations['diasParaAguinaldo'] * $calculations['sd']) < $calculations['valorAguinaldoExento']) 
									{
										$calculations['aguinaldoExento'] = $calculations['diasParaAguinaldo'] * $calculations['sd'];
									}
									else
									{
										$calculations['aguinaldoExento'] = $calculations['valorAguinaldoExento'];
									}

									if (($calculations['diasParaAguinaldo'] * $calculations['sd']) > $calculations['valorAguinaldoExento']) 
									{
										$calculations['aguinaldoGravable'] = ($calculations['diasParaAguinaldo'] * $calculations['sd'])-$calculations['aguinaldoExento'];
									}
									else
									{
										$calculations['aguinaldoGravable'] = 0;
									}


									//-------- PERCEPCIONES PRIMA VACACIONAL ---------------------------------------------------------------


									if (($calculations['vacaciones']*$calculations['prima_vac_esp'])<$calculations['valorPrimaVacacaionalExenta'])
									{
										$calculations['primaVacacionalExenta'] = round($calculations['vacaciones']*$calculations['prima_vac_esp'],6);
									}
									else
									{
										$calculations['primaVacacionalExenta'] = $calculations['valorPrimaVacacaionalExenta'];
									}

									if (($calculations['vacaciones']*$calculations['prima_vac_esp'])>$calculations['valorPrimaVacacaionalExenta'])
									{
										$calculations['primaVacacionalGravada'] = round(($calculations['vacaciones']*$calculations['prima_vac_esp'])-$calculations['primaVacacionalExenta'],6);
									}
									else
									{
										$calculations['primaVacacionalGravada'] = 0;
									}

									$calculations['otrasPercepciones'] = $request->other_perception[$i];

									$calculations['totalPercepciones'] = round($calculations['primaAntiguedad'],2)+round($calculations['vacaciones'],2)+round($calculations['aguinaldoExento'],2)+round($calculations['aguinaldoGravable'],2)+round($calculations['primaVacacionalExenta'],2)+round($calculations['primaVacacionalGravada'],2)+round($calculations['otrasPercepciones'],2);

									// ------------------------------------------------------------------------------------
								}

								//-------- RETENCIONES ----------------------------------------------------------------

								// ISR 1ER FRACCION

								$calculations['baseISR_fraccion1']			= round(((($calculations['vacaciones']+$calculations['aguinaldoGravable']+$calculations['primaVacacionalGravada'])/365)*30.4)+($calculations['sd']*30),6);
								$parameterISRF1								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion1'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion1']	= $parameterISRF1->last()->inferior;
								$calculations['excedente_fraccion1']		= round($calculations['baseISR_fraccion1']-$calculations['limiteInferior_fraccion1'],6);
								$calculations['factor_fraccion1']			= round($parameterISRF1->last()->excess/100,6);
								$calculations['isrMarginal_fraccion1']		= round($calculations['excedente_fraccion1'] * $calculations['factor_fraccion1'],6);
								$calculations['cuotaFija_fraccion1']		= round($parameterISRF1->last()->quota,6);
								$calculations['isr_fraccion1']				= round($calculations['isrMarginal_fraccion1']+$calculations['cuotaFija_fraccion1'],6);

								// ISR 2DA FRACCION

								$calculations['baseISR_fraccion2']			= round($calculations['sd']*30,6);
								$parameterISRF2								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion2'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion2']	= $parameterISRF2->last()->inferior;
								$calculations['excedente_fraccion2']		= round($calculations['baseISR_fraccion2']-$calculations['limiteInferior_fraccion2'],6);
								$calculations['factor_fraccion2']			= round($parameterISRF2->last()->excess/100,6);
								$calculations['isrMarginal_fraccion2']		= round($calculations['excedente_fraccion2'] * $calculations['factor_fraccion2'],6);
								$calculations['cuotaFija_fraccion2']		= round($parameterISRF2->last()->quota,6);
								$calculations['isr_fraccion2']				= round($calculations['isrMarginal_fraccion2']+$calculations['cuotaFija_fraccion2'],6);

								$calculations['resta']		= round($calculations['isr_fraccion1']-$calculations['isr_fraccion2'],6);
								if ($calculations['resta'] == 0) 
								{
									$calculations['factor1']	= 0;
									$calculations['factor2']	= 0;
									$calculations['isr']		= 0;
								}
								else
								{
									$calculations['factor1']	= round(((($calculations['vacaciones']+$calculations['aguinaldoGravable']+$calculations['primaVacacionalGravada'])/365)*30.4),6);
									$calculations['factor2']	= round($calculations['resta']/$calculations['factor1'],6);
									$calculations['isr']		= round($calculations['factor2']*($calculations['vacaciones']+$calculations['aguinaldoGravable']+$calculations['primaVacacionalGravada']),6);
								}

								// ISR FINIQUITO (INDEMNIZACION)

								$calculations['baseTotalDePercepciones']	= round($calculations['sd']*30,6);
								$calculations['baseISR_finiquito']			= $calculations['baseTotalDePercepciones'];
								
								$parameterISRFiniquito						= App\ParameterISR::where('inferior','<=',$calculations['baseISR_finiquito'])->where('lapse',30)->get();
								
								$calculations['limiteInferior_finiquito']	= $parameterISRFiniquito->last()->inferior;
								$calculations['excedente_finiquito']		= round($calculations['baseISR_finiquito']-$calculations['limiteInferior_finiquito'],6);
								$calculations['factor_finiquito']			= round($parameterISRFiniquito->last()->excess/100,6);
								$calculations['isrMarginal_finiquito']		= round($calculations['excedente_finiquito'] * $calculations['factor_finiquito'],6);
								$calculations['cuotaFija_finiquito']		= round($parameterISRFiniquito->last()->quota,6);
								$calculations['isr_salario']				= round($calculations['isrMarginal_finiquito']+$calculations['cuotaFija_finiquito'],6);
								
								$calculations['isr_finiquito']				= round(($calculations['isr_salario']/$calculations['baseTotalDePercepciones'])*$calculations['indemnizacionGravada'],6);
								
								$calculations['totalISR']					= $calculations['isr_finiquito'] + $calculations['isr']; 
								$calculations['totalRetenciones']			= round($calculations['totalISR'],2);

								// --------------------------------------------------------------------------------------------

								
								$calculations['netIncome']			= $calculations['totalPercepciones']-$calculations['totalRetenciones'];

								$arrayNomina[$i]['curp']				= $t_nominaemployee->employee->first()->curp;
								$arrayNomina[$i]['empresa']			= $t_nominaemployee->workerData->first()->enterprises->name;
								$arrayNomina[$i]['proyecto']			= $t_nominaemployee->workerData->first()->projects()->exists() ? $t_nominaemployee->workerData->first()->projects->proyectName : "Sin Proyecto";
								$arrayNomina[$i]['nombre']				= $t_nominaemployee->employee->first()->name;
								$arrayNomina[$i]['apellido_paterno']	= $t_nominaemployee->employee->first()->last_name;
								$arrayNomina[$i]['apellido_materno']	= $t_nominaemployee->employee->first()->scnd_last_name;
								$arrayNomina[$i]['monto_total']		= round($request->netIncome[$i],2);
								$arrayNomina[$i]['monto_fiscal']		= round($calculations['netIncome'],2);
								$arrayNomina[$i]['complemento']		= round($request->netIncome[$i]-$calculations['netIncome'],2);

								$calculations = [];

							}

							break;

						case '005':
							$arrayNomina = [];
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee				= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								

								// ----- calculo para dias de vacaciones ---------------------------
								$calculations					= [];
								$calculations['fechaIngreso']	= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$calculations['fechaActual']	= Carbon::now();
								$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
								$calculations['yearsWork']		= ceil($calculations['diasTrabajados']/365);
								if ($calculations['yearsWork'] > 24) 
								{
									$calculations['vacationDays']	= 20;
								}
								else
								{
									$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
								}

								//------------------------------------------------------------------
								
								$calculations['prima_vac_esp']		= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
								$calculations['sdi']				= $t_nominaemployee->workerData->first()->sdi;
								$calculations['sd']					= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);
								
								$calculations['diasTrabajadosM']	= $request->worked_days[$i];
								
								$calculations['diasParaVacaciones']	= ($calculations['vacationDays']*$calculations['diasTrabajadosM'])/365;
								
								$calculations['uma']				= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
								$calculations['exento']				= $calculations['uma']*15; 

								//-------- PERCEPCIONES --------------------------------------------------------------

								$calculations['vacaciones'] = $calculations['sd']*$calculations['diasParaVacaciones'];

								if (($calculations['sd']*$calculations['diasParaVacaciones']*$calculations['prima_vac_esp'])<$calculations['exento'])
								{
									$calculations['primaVacacionalExenta'] = round($calculations['sd']*$calculations['diasParaVacaciones']*$calculations['prima_vac_esp'],6);
								}
								else
								{
									$calculations['primaVacacionalExenta'] = $calculations['exento'];
								}

								if (($calculations['sd']*$calculations['diasParaVacaciones']*$calculations['prima_vac_esp'])>$calculations['exento'])
								{
									$calculations['primaVacacionalGravada'] = round(($calculations['sd']*$calculations['diasParaVacaciones']*$calculations['prima_vac_esp'])-$calculations['primaVacacionalExenta'],6);
								}
								else
								{
									$calculations['primaVacacionalGravada'] = 0;
								}

								$calculations['totalPercepciones'] = round($calculations['primaVacacionalExenta'],2)+round($calculations['primaVacacionalGravada'],2);

								// ------------------------------------------------------------------------------------

								//-------- RETENCIONES ----------------------------------------------------------------

								// ISR 1ER FRACCION

								$calculations['baseISR_fraccion1']			= round((($calculations['primaVacacionalGravada']/365)*30.4)+($calculations['sd']*30),6);
								$parameterISRF1								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion1'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion1']	= $parameterISRF1->last()->inferior;
								$calculations['excedente_fraccion1']		= round($calculations['baseISR_fraccion1']-$calculations['limiteInferior_fraccion1'],6);
								$calculations['factor_fraccion1']			= round($parameterISRF1->last()->excess/100,6);
								$calculations['isrMarginal_fraccion1']		= round($calculations['excedente_fraccion1'] * $calculations['factor_fraccion1'],6);
								$calculations['cuotaFija_fraccion1']		= round($parameterISRF1->last()->quota,6);
								$calculations['isr_fraccion1']				= round($calculations['isrMarginal_fraccion1']+$calculations['cuotaFija_fraccion1'],6);

								// ISR 2DA FRACCION

								$calculations['baseISR_fraccion2']			= round($calculations['sd']*30,6);
								$parameterISRF2								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion2'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion2']	= $parameterISRF2->last()->inferior;
								$calculations['excedente_fraccion2']		= round($calculations['baseISR_fraccion2']-$calculations['limiteInferior_fraccion2'],6);
								$calculations['factor_fraccion2']			= round($parameterISRF2->last()->excess/100,6);
								$calculations['isrMarginal_fraccion2']		= round($calculations['excedente_fraccion2'] * $calculations['factor_fraccion2'],6);
								$calculations['cuotaFija_fraccion2']		= round($parameterISRF2->last()->quota,6);
								$calculations['isr_fraccion2']				= round($calculations['isrMarginal_fraccion2']+$calculations['cuotaFija_fraccion2'],6);

								$calculations['resta']		= round($calculations['isr_fraccion1']-$calculations['isr_fraccion2'],6);
								if ($calculations['resta'] == 0) 
								{
									$calculations['factor1']	= 0;
									$calculations['factor2']	= 0;
									$calculations['isr']		= 0;
								}
								else
								{
									$calculations['factor1']	= round((($calculations['primaVacacionalGravada']/365) * 30.4),6);
									$calculations['factor2']	= round($calculations['resta']/$calculations['factor1'],6);
									$calculations['isr']		= round($calculations['factor2']*$calculations['primaVacacionalGravada'],6);
								}

								$calculations['totalRetenciones'] = round($calculations['isr'],2);

								// --------------------------------------------------------------------------------------------

								
								$calculations['netIncome']			= $calculations['totalPercepciones']-$calculations['totalRetenciones'];

								$arrayNomina[$i]['curp']				= $t_nominaemployee->employee->first()->curp;
								$arrayNomina[$i]['empresa']				= $t_nominaemployee->workerData->first()->enterprises->name;
								$arrayNomina[$i]['proyecto']			= $t_nominaemployee->workerData->first()->projects()->exists() ? $t_nominaemployee->workerData->first()->projects->proyectName : "Sin Proyecto";
								$arrayNomina[$i]['nombre']				= $t_nominaemployee->employee->first()->name;
								$arrayNomina[$i]['apellido_paterno']	= $t_nominaemployee->employee->first()->last_name;
								$arrayNomina[$i]['apellido_materno']	= $t_nominaemployee->employee->first()->scnd_last_name;
								$arrayNomina[$i]['monto_total']			= round($request->netIncome[$i],2);
								$arrayNomina[$i]['monto_fiscal']		= round($calculations['netIncome'],2);
								$arrayNomina[$i]['complemento']			= round($request->netIncome[$i]-$calculations['netIncome'],2);
								$calculations = [];

							}
						
							break;

						case '006':
							$arrayNomina			= [];
							$t_nomina			= App\Nomina::find($t_request->nominasReal->first()->idnomina);
							$sumaDiasTrabajados	= 0;
							$sumaSueldoTotal	= 0;
							//------- calculo para sumatoria de dias trabajados y sueldo total ------------------------
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{
								$t_nominaemployee				= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								
								$sumaDiasTrabajados		 		+= $request->worked_days[$i];
								$calculations					= [];
								$calculations['fechaIngreso']	= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$calculations['fechaActual']	= Carbon::now();
								$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
								$calculations['yearsWork']		= ceil($calculations['diasTrabajados']/365);
								if ($calculations['yearsWork'] > 24) 
								{
									$calculations['vacationDays']	= 20;
								}
								else
								{
									$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
								}


								$calculations['prima_vac_esp']	= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
								$calculations['sdi']			= $t_nominaemployee->workerData->first()->sdi;
								$calculations['sd']				= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);

								$sumaSueldoTotal += round($request->worked_days[$i] * $calculations['sd'],6);
								$calculations = [];
							}

							// -------------------------------------------------------------------------------------------------------
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee				= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$t_nominaemployee->worked_days	= $request->worked_days[$i];
								$t_nominaemployee->save();

								// ----- calculo para dias de vacaciones ---------------------------
								$calculations					= [];
								$calculations['fechaIngreso']	= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$calculations['fechaActual']	= Carbon::now();
								$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
								$calculations['yearsWork']		= ceil($calculations['diasTrabajados']/365);
								if ($calculations['yearsWork'] > 24) 
								{
									$calculations['vacationDays']	= 20;
								}
								else
								{
									$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
								}

								//------------------------------------------------------------------
								
								$calculations['prima_vac_esp']		= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
								$calculations['sdi']				= $t_nominaemployee->workerData->first()->sdi;
								$calculations['sd']					= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);
								
								$calculations['diasTrabajadosM']	= $request->worked_days[$i];
								$calculations['sueldoTotal']		= round($calculations['diasTrabajadosM'] * $calculations['sd'],6);
								
								$calculations['sumaDiasTrabajados']	= $sumaDiasTrabajados;
								$calculations['sumaSueldoTotal']	= $sumaSueldoTotal;

								$calculations['uma']							= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
								$calculations['exento']							= $calculations['uma']*15; 

								$calculations['ptuPorPagar']		= round($request->ptu_to_pay,6);
								$calculations['factorPorDias']		= round(($calculations['ptuPorPagar']/2)/$calculations['sumaDiasTrabajados'],6);
								$calculations['factorPorSueldo']	= round(($calculations['ptuPorPagar']/2)/$calculations['sumaSueldoTotal'],6);

								$calculations['ptuPorDias']		= round($calculations['diasTrabajadosM'] * $calculations['factorPorDias'],6);
								$calculations['ptuPorSueldos']	= round($calculations['sueldoTotal']*$calculations['factorPorSueldo'],6);
								$calculations['ptuTotal']		= round($calculations['ptuPorDias']+$calculations['ptuPorSueldos'],6);

								//-------- PERCEPCIOONES -------------------------------------------------------------

								$calculations['ptuExenta']			= $calculations['exento'];
								$calculations['ptuGravada']			= round($calculations['ptuTotal']-$calculations['ptuExenta'],6);
								$calculations['totalPercepciones']	= round($calculations['ptuExenta'],2)+round($calculations['ptuGravada'],2);


								// ------------------------------------------------------------------------------------

								//-------- RETENCIONES ----------------------------------------------------------------

								// ISR 1ER FRACCION

								$calculations['baseISR_fraccion1']			= round((($calculations['ptuGravada']/365)*30.4)+($calculations['sd']*30),6);
								$parameterISRF1								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion1'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion1']	= $parameterISRF1->last()->inferior;
								$calculations['excedente_fraccion1']		= round($calculations['baseISR_fraccion1']-$calculations['limiteInferior_fraccion1'],6);
								$calculations['factor_fraccion1']			= round($parameterISRF1->last()->excess/100,6);
								$calculations['isrMarginal_fraccion1']		= round($calculations['excedente_fraccion1'] * $calculations['factor_fraccion1'],6);
								$calculations['cuotaFija_fraccion1']		= round($parameterISRF1->last()->quota,6);
								$calculations['isr_fraccion1']				= round($calculations['isrMarginal_fraccion1']+$calculations['cuotaFija_fraccion1'],6);

								// ISR 2DA FRACCION

								$calculations['baseISR_fraccion2']			= round($calculations['sd']*30,6);
								$parameterISRF2								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion2'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion2']	= $parameterISRF2->last()->inferior;
								$calculations['excedente_fraccion2']		= round($calculations['baseISR_fraccion2']-$calculations['limiteInferior_fraccion2'],6);
								$calculations['factor_fraccion2']			= round($parameterISRF2->last()->excess/100,6);
								$calculations['isrMarginal_fraccion2']		= round($calculations['excedente_fraccion2'] * $calculations['factor_fraccion2'],6);
								$calculations['cuotaFija_fraccion2']		= round($parameterISRF2->last()->quota,6);
								$calculations['isr_fraccion2']				= round($calculations['isrMarginal_fraccion2']+$calculations['cuotaFija_fraccion2'],6);

								$calculations['resta']		= round($calculations['isr_fraccion1']-$calculations['isr_fraccion2'],6);
								$calculations['factor1']	= round((($calculations['ptuGravada']/365) * 30.4),6);
								if($calculations['factor1'] == 0)
								{
									$calculations['factor2']	= 0;
								}
								else
								{
									$calculations['factor2']	= round($calculations['resta']/$calculations['factor1'],6);
								}

								$calculations['isr']		= round($calculations['factor2']*$calculations['ptuGravada'],6);

								$calculations['totalRetenciones'] = round($calculations['isr'],2);

								// --------------------------------------------------------------------------------------------

								
								$calculations['netIncome']			= $calculations['totalPercepciones']-$calculations['totalRetenciones'];

								$arrayNomina[$i]['curp']				= $t_nominaemployee->employee->first()->curp;
								$arrayNomina[$i]['empresa']				= $t_nominaemployee->workerData->first()->enterprises->name;
								$arrayNomina[$i]['proyecto']			= $t_nominaemployee->workerData->first()->projects()->exists() ? $t_nominaemployee->workerData->first()->projects->proyectName : "Sin Proyecto";
								$arrayNomina[$i]['nombre']				= $t_nominaemployee->employee->first()->name;
								$arrayNomina[$i]['apellido_paterno']	= $t_nominaemployee->employee->first()->last_name;
								$arrayNomina[$i]['apellido_materno']	= $t_nominaemployee->employee->first()->scnd_last_name;
								$arrayNomina[$i]['monto_total']			= round($request->netIncome[$i],2);
								$arrayNomina[$i]['monto_fiscal']		= round($calculations['netIncome'],2);
								$arrayNomina[$i]['complemento']			= round($request->netIncome[$i]-$calculations['netIncome'],2);

								$calculations = [];

							}
							break;

						
						default:
							# code...
							break;
					}
				}
				else
				{
					for ($i=0; $i < count($request->request_idnominaEmployee); $i++) 
					{ 
						$t_nominaemployee						= App\NominaEmployee::find($request->request_idnominaEmployee[$i]);
						$arrayNomina[$i]['curp']				= $t_nominaemployee->employee->first()->curp;
						$arrayNomina[$i]['empresa']				= $t_nominaemployee->workerData->first()->enterprises->name;
						$arrayNomina[$i]['proyecto']			= $t_nominaemployee->workerData->first()->projects()->exists() ? $t_nominaemployee->workerData->first()->projects->proyectName : "Sin Proyecto";
						$arrayNomina[$i]['apellido_paterno']	= $t_nominaemployee->employee->first()->last_name;
						$arrayNomina[$i]['apellido_materno']	= $t_nominaemployee->employee->first()->scnd_last_name;
						$arrayNomina[$i]['monto_total']			= round($request->request_netIncome[$i],2);
						$arrayNomina[$i]['monto_fiscal']		= 0;
						$arrayNomina[$i]['complemento']			= round($request->request_complement[$i],2);
					}
				}

				if($arrayNomina != null)
				{
					$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
					$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
					$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setFontBold()->build();
					$writer			= WriterEntityFactory::createXLSXWriter();
					$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Formato de nómina.xlsx');
					$writer->getCurrentSheet()->setName('EMP PREEX');
					$headerArray	= ['REGISTRO DE EMPLEADOS PREEXISTENTES','','','','','','','','',''];
					$tempHeaders		= [];
					foreach($headerArray as $k => $header)
					{
						$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
					}
					$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
					$writer->addRow($rowFromValues);

					$subHeaderArray	= ['CURP','EMPRESA','PROYECTO','NOMBRE','APELLIDO_PATERNO','APELLIDO_MATERNO','MONTO_FISCAL','MONTO_TOTAL','COMPLEMENTO','NOTA'];
					
					$tempHeaders	= [];
					foreach($subHeaderArray as $k => $subHeader)
					{
						$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol1);
					}
					$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
					$writer->addRow($rowFromValues);
					$tempCurp	= '';
					$kindRow			= true;
					$flagAlimony 		= false;
					foreach($arrayNomina as $key => $nom)
					{
						if($tempCurp != $nom['curp'])
						{
							$tempCurp = $nom['curp'];
							$kindRow = !$kindRow;
							
						}
						$tmpArr = [];
						$tmpArr[] = WriterEntityFactory::createCell($nom['curp']);
						$tmpArr[] = WriterEntityFactory::createCell($nom['empresa']);
						$tmpArr[] = WriterEntityFactory::createCell($nom['proyecto']);
						$tmpArr[] = WriterEntityFactory::createCell($nom['nombre']);
						$tmpArr[] = WriterEntityFactory::createCell($nom['apellido_paterno']);
						$tmpArr[] = WriterEntityFactory::createCell($nom['apellido_materno']);
						$tmpArr[] = WriterEntityFactory::createCell($nom['monto_fiscal']);
						$tmpArr[] = WriterEntityFactory::createCell($nom['monto_total']);
						$tmpArr[] = WriterEntityFactory::createCell($nom['complemento']);
						$tmpArr[] = WriterEntityFactory::createCell('');

						if($kindRow)
						{
							$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
						}
						else
						{
							$rowFromValues = WriterEntityFactory::createRow($tmpArr);
						}
						$writer->addRow($rowFromValues);
					
					}
					return $writer->close();
				}
				
			}
		}
	}

	public function getNominaPrecalculateFull(Request $request,$id)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$arraySalary = $arrayBonus = $arrayLiquidation = $arrayVP = $arrayPS = [];
			$t_request				= App\RequestModel::find($id);

			$checkFiscal = App\RequestModel::where('kind',16)
				->where('idprenomina',$t_request->idprenomina)
				->where('idDepartment',$t_request->idDepartment)
				->where('taxPayment',1)
				->whereNotIn('folio',[$id])
				->first();

			$flagCheckFiscal = true;

			if ($flagCheckFiscal) 
			{
				$flag = false;
				$request_type	= App\RequestModel::find($id);

				if ($request_type->taxPayment == 1) 
				{
					switch ($request->type_payroll) 
					{
						case '001':
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee		= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$admissionDate			= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$sdi					= $t_nominaemployee->workerData->first()->sdi;
								$primaDeRiesgoDeTrabajo	= isset(App\EmployerRegister::where('employer_register',$t_nominaemployee->workerData->first()->employer_register)->first()->risk_number) ? App\EmployerRegister::where('employer_register',$t_nominaemployee->workerData->first()->employer_register)->first()->risk_number : ''; 

								if ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null || $primaDeRiesgoDeTrabajo == '' || $primaDeRiesgoDeTrabajo == null) 
								{
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con los siguientes datos: Fecha de Alta, SDI y Registro patronal","error");';

									return back()->with('alert',$alert);
								}

								$calculations = [];
								$calculations['admissionDate']  = $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								if (new \DateTime($request->from_date[$i]) < new \DateTime($calculations['admissionDate'])) 
								{
									$datetime1 = date_create($request->from_date[$i]);
									$datetime2 = date_create($calculations['admissionDate']);
									$interval  = date_diff($datetime1, $datetime2);
									$daysStart = $interval->format('%a');
								}
								else
								{
									$daysStart = 0;
								}
								$downDate = $t_nominaemployee->workerData->first()->downDate != '' && new \DateTime($t_nominaemployee->workerData->first()->downDate) > new \DateTime($calculations['admissionDate']) ? $t_nominaemployee->workerData->first()->downDate : null;
								$daysDown = 0;
								if ($downDate !='' && new \DateTime($downDate) >= new \DateTime($request->from_date[$i]) && new \DateTime($downDate) <= new \DateTime($request->to_date[$i])) 
								{
									$date1    = new \DateTime($downDate);
									$date2    = new \DateTime($request->to_date[$i]);
									$diff     = $date1->diff($date2);
									$daysDown = $diff->days;
								}
								else
								{
									$daysDown = 0;
								}
								$calculations['workedDays']  = (App\CatPeriodicity::find($request->periodicity[$i])->days)-$request->absence[$i]-$daysStart-$daysDown;

								if ($calculations['workedDays'] < 1) 
								{
									$alert = 'swal("","Error, El empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' no puede tener 0 días trabajados. Por favor verifique los siguientes datos: Faltas, Fecha de Alta, Fecha de Inicial y Final de Pago.","error");';
									return back()->with('alert',$alert);
								}
							}
							break;

						case '002':
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee	= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$paymentWay			= $request->paymentWay[$i];
								$idemployeeAccount	= $request->idemployeeAccount[$i];
								//calculo para dias de vacaciones
								$admissionDate		= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$sdi			= $t_nominaemployee->workerData->first()->sdi;
								
								if ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null || $paymentWay == null || $paymentWay == '') 
								{
									
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con los siguientes datos: Fecha de Alta, SDI, Forma de pago","error");';

									return back()->with('alert',$alert);
								}
								if ($paymentWay == 1 && ($idemployeeAccount == '' || $idemployeeAccount== null)) 
								{
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con una cuenta bancaria o cambie la forma de pago a Efectivo","error");';

									return back()->with('alert',$alert);
								}
								if ($paymentWay == 2 && ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null))
								{
									
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con los siguientes datos: Fecha de Alta, SDI, Forma de pago","error");';

									return back()->with('alert',$alert);
								}

							}
							break;

						case '003':
						case '004':
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee	= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$paymentWay			= $request->paymentWay[$i];
								$idemployeeAccount	= $request->idemployeeAccount[$i];
								$admissionDate		=  $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$sdi				= $t_nominaemployee->workerData->first()->sdi;

								if ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null || $paymentWay == null || $paymentWay == '') 
								{
									
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con los siguientes datos: Fecha de Alta, SDI, Forma de pago","error");';

									return back()->with('alert',$alert);
								}
								if ($paymentWay == 1 && ($idemployeeAccount == '' || $idemployeeAccount== null)) 
								{
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con una cuenta bancaria o cambie la forma de pago a Efectivo","error");';

									return back()->with('alert',$alert);
								}
								if ($paymentWay == 2 && ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null))
								{
									
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con los siguientes datos: Fecha de Alta, SDI, Forma de pago","error");';

									return back()->with('alert',$alert);
								}
							}

							break;

						case '005':
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee	= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$admissionDate		= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$sdi				= $t_nominaemployee->workerData->first()->sdi;
								$paymentWay			= $request->paymentWay[$i];
								$idemployeeAccount	= $request->idemployeeAccount[$i];
								
								if ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null || $paymentWay == null || $paymentWay == '') 
								{
									
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con los siguientes datos: Fecha de Alta, SDI, Forma de pago","error");';

									return back()->with('alert',$alert);
								}
								if ($paymentWay == 1 && ($idemployeeAccount == '' || $idemployeeAccount== null)) 
								{
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con una cuenta bancaria o cambie la forma de pago a Efectivo","error");';

									return back()->with('alert',$alert);
								}
								if ($paymentWay == 2 && ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null))
								{
									
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con los siguientes datos: Fecha de Alta, SDI, Forma de pago","error");';

									return back()->with('alert',$alert);
								}
							}
							break;

						case '006':
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee	= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$paymentWay			= $request->paymentWay[$i];
								$idemployeeAccount	= $request->idemployeeAccount[$i];
								$admissionDate		= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$sdi				= $t_nominaemployee->workerData->first()->sdi;
								
								if ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null || $paymentWay == null || $paymentWay == '') 
								{
									
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con los siguientes datos: Fecha de Alta, SDI, Forma de pago","error");';

									return back()->with('alert',$alert);
								}
								if ($paymentWay == 1 && ($idemployeeAccount == '' || $idemployeeAccount== null)) 
								{
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con una cuenta bancaria o cambie la forma de pago a Efectivo","error");';

									return back()->with('alert',$alert);
								}
								if ($paymentWay == 2 && ($admissionDate == '' || $admissionDate == null || $sdi == '' || $sdi == null))
								{
									
									$alert = 'swal("","Error, revise que el empleado '.$t_nominaemployee->employee->first()->name.' '.$t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' cuente con los siguientes datos: Fecha de Alta, SDI, Forma de pago","error");';

									return back()->with('alert',$alert);
								}

							}
							break;
					}
				}
				
				$t_request 		= App\RequestModel::find($id);
				$totalRequest 	= 0;

				$t_nomina = App\Nomina::find($t_request->nominasReal->first()->idnomina);

				if ($t_request->taxPayment == 1) 
				{
					switch ($request->type_payroll) 
					{
						case '001':
							$arrayNomina = [];
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee					= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);

								$calculations = [];
								//calculo para dias de vacaciones
									$calculations['admissionDate']	= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
									$calculations['nowDate']		= Carbon::now();
									$calculations['diasTrabajados'] = App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['admissionDate'],$calculations['nowDate']);
									$calculations['yearsWork']		= ceil($calculations['diasTrabajados']/365);
									if ($calculations['yearsWork'] > 24) 
									{
										$calculations['vacationDays']	= 20;
									}
									else
									{
										$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
									}

								//-------------------

								$calculations['prima_vac_esp']	= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
								$calculations['sdi']			= $t_nominaemployee->workerData->first()->sdi;
								$calculations['sd']				= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);

								$daysStart = 0;	
								if (new \DateTime($request->from_date[$i]) < new \DateTime($calculations['admissionDate'])) 
								{
									$datetime1	= date_create($request->from_date[$i]);
									$datetime2	= date_create($calculations['admissionDate']);
									$interval	= date_diff($datetime1, $datetime2);

									$daysStart = $interval->format('%a');

								}
								else
								{
									$daysStart = 0;
								}

								$downDate = $t_nominaemployee->workerData->first()->downDate != '' && new \DateTime($t_nominaemployee->workerData->first()->downDate) > new \DateTime($calculations['admissionDate']) ? $t_nominaemployee->workerData->first()->downDate : null;
								$daysDown = 0;
								if ($downDate !='' && new \DateTime($downDate) >= new \DateTime($request->from_date[$i]) && new \DateTime($downDate) <= new \DateTime($request->to_date[$i])) 
								{
									$date1		= new \DateTime($downDate);
									$date2		= new \DateTime($request->to_date[$i]);
									$diff		= $date1->diff($date2);
									$daysDown	= $diff->days;

								}
								else
								{
									$daysDown = 0;
								}

								$calculations['workedDays']		= (App\CatPeriodicity::find($request->periodicity[$i])->days)-$request->absence[$i]-$daysStart-$daysDown;

								$calculations['periodicity']	= App\CatPeriodicity::find($request->periodicity[$i])->description;
								$calculations['rangeDate']		= $request->from_date[$i].' '.$request->to_date[$i];

								switch ($request->periodicity[$i]) 
								{
									case '02':
										$d = new DateTime($request->from_date[$i]);
										$d->modify('next thursday');
										$calculations['divisorDayFormImss'] = App\Http\Controllers\AdministracionNominaController::days_count($d->format('m'),$d->format('Y'),4);
										break;

									case '04':
										$calculations['divisorDayFormImss'] = 2;
										break;

									case '05':
										$calculations['divisorDayFormImss'] = 1;
										break;
									
									default:
										break;
								}
								$d = new DateTime($request->from_date[$i]);
								$d->modify('next thursday');
								$calculations['daysMonth'] 		= App\Http\Controllers\AdministracionNominaController::days_month($d->format('m'),$d->format('Y'));

								if ($t_nominaemployee->WorkerData->first()->regime_id == '09') 
								{
									$calculations['daysForImss']	= 0;
								}
								else
								{
									switch ($request->periodicity[$i]) 
									{
										case '02':
											if ($calculations['workedDays']<7) 
											{
												$calculations['daysForImss']	= $calculations['workedDays'];
											}
											else
											{
												$calculations['daysForImss']	= $calculations['daysMonth']/$calculations['divisorDayFormImss'];
											}
											break;

										case '04':
											if ($calculations['workedDays']<15) 
											{
												$calculations['daysForImss']	= $calculations['workedDays'];
											}
											else
											{
												$calculations['daysForImss']	= $calculations['daysMonth']/$calculations['divisorDayFormImss'];
											}
											break;

										case '05':
											if ($calculations['workedDays']<30) 
											{
												$calculations['daysForImss']	= $calculations['workedDays'];
											}
											else
											{
												$calculations['daysForImss']	= $calculations['daysMonth']/$calculations['divisorDayFormImss'];
											}
											break;
										
										default:
											# code...
											break;
									}
								}
								
								
								//PERCEPCIONES
								$calculations['salary']			= $calculations['sd']*$calculations['workedDays'];
								$calculations['loanPerception']	= $request->loan_perception[$i];
								$calculations['puntuality']		= $calculations['salary'] * (($t_nominaemployee->workerData->first()->bono/100)/2);
								$calculations['assistance']		= $calculations['salary'] * (($t_nominaemployee->workerData->first()->bono/100)/2);

								//calculo para el subsidio

								$calculations['baseTotalDePercepciones']	= $calculations['salary'] + $calculations['puntuality'] + $calculations['assistance'];
								$calculations['baseISR']					= ($calculations['baseTotalDePercepciones']/$calculations['workedDays'])*30.4;
								
								$parameterISR								= App\ParameterISR::where('inferior','<=',$calculations['baseISR'])->where('lapse',30)->get();
								
								$calculations['limiteInferior']			= $parameterISR->last()->inferior;
								$calculations['excedente']				= $calculations['baseISR']-$calculations['limiteInferior'];
								$calculations['factor']					= $parameterISR->last()->excess/100;
								$calculations['isrMarginal']			= $calculations['excedente'] * $calculations['factor'];
								$calculations['cuotaFija']				= $parameterISR->last()->quota;
								$calculations['isrAntesDelSubsidio']	= (($calculations['isrMarginal'] + $calculations['cuotaFija'])/30.4)*$calculations['workedDays'];
								$parameterSubsidy						= App\ParameterSubsidy::where('inferior','<=',$calculations['baseISR'])->where('lapse',30)->get();

								if ($calculations['baseISR'] <= 7382.34) 
								{
									$calculations['subsidioAlEmpleo'] = ($parameterSubsidy->last()->subsidy/30.4)*$calculations['workedDays'];
								}
								else
								{
									$calculations['subsidioAlEmpleo'] = 0;
								}

								if (($calculations['isrAntesDelSubsidio'] - $calculations['subsidioAlEmpleo']) > 0) 
								{
									$calculations['isrARetener']	= $calculations['isrAntesDelSubsidio'] - $calculations['subsidioAlEmpleo'];
									$calculations['subsidio']		= 0; 	
								}
								else
								{
									$calculations['isrARetener']	= 0;
									$calculations['subsidio']		= round(($calculations['isrAntesDelSubsidio'] - $calculations['subsidioAlEmpleo'])*(-1),2); 	
								}

								$calculations['totalPerceptions']	= round($calculations['salary'],2) + round($calculations['loanPerception'],2) + round($calculations['puntuality'],2) + round($calculations['assistance'],2) + round($calculations['subsidio'],2);

								
								//----------------------------

								//RETENCIONES

								// calculo de IMSS (cuotas obrero-patronal)
								$calculations['SalarioBaseDeCotizacion']	= $calculations['sdi'];
								$calculations['diasDelPeriodoMensual']		= $calculations['daysForImss'];
								$calculations['diasDelPeriodoBimestral']	= $calculations['daysForImss'];
								$calculations['uma']						= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
								$calculations['primaDeRiesgoDeTrabajo']		= App\EmployerRegister::where('employer_register',$t_nominaemployee->workerData->first()->employer_register)->first()->risk_number; 
								
								if (($calculations['uma']*3) > $calculations['SalarioBaseDeCotizacion'])
								{
									$calculations['imssExcedente'] 			= 0;
								}
								else
								{
									$calculations['imssExcedente']			= ((($calculations['SalarioBaseDeCotizacion']-(3*$calculations['uma']))*$calculations['diasDelPeriodoMensual'])*0.4)/100;
								}
								$calculations['prestacionesEnDinero']		= (($calculations['SalarioBaseDeCotizacion']*$calculations['diasDelPeriodoMensual'])*0.25)/100;
								$calculations['gastosMedicosPensionados']	= (($calculations['SalarioBaseDeCotizacion']*$calculations['diasDelPeriodoMensual'])*0.375)/100;
								$calculations['invalidezVidaPatronal']		= (($calculations['SalarioBaseDeCotizacion']*$calculations['diasDelPeriodoMensual'])*0.625)/100;
								$calculations['cesantiaVejez']				= (($calculations['SalarioBaseDeCotizacion']*$calculations['diasDelPeriodoMensual'])*1.125)/100;

								$calculations['imss'] = $calculations['imssExcedente']+$calculations['prestacionesEnDinero']+$calculations['gastosMedicosPensionados']+$calculations['invalidezVidaPatronal']+$calculations['cesantiaVejez'];

								//calculo infonavit

								$calculations['diasBimestre']		= App\Http\Controllers\AdministracionNominaController::days_bimester($request->from_date[$i]);
								$calculations['factorInfonavit']	= App\Parameter::where('parameter_name','INFONAVIT_FACTOR')->first()->parameter_value;

								if ($t_nominaemployee->workerData->first()->infonavitDiscountType != '') 
								{
									$calculations['descuentoEmpleado']	= $t_nominaemployee->workerData->first()->infonavitDiscount;
									$calculations['quinceBimestral']	= App\Http\Controllers\AdministracionNominaController::pay_infonavit($request->from_date[$i],$request->to_date[$i]);
									switch ($t_nominaemployee->workerData->first()->infonavitDiscountType) 
									{
										case 1:
											$calculations['descuentoInfonavitTemp'] = (($calculations['descuentoEmpleado']*$calculations['factorInfonavit']*2)/$calculations['diasBimestre'])*$calculations['daysForImss']+$calculations['quinceBimestral']; 
											break;

										case 2:
											$calculations['descuentoInfonavitTemp'] = $calculations['descuentoEmpleado']*2/$calculations['diasBimestre']*$calculations['daysForImss']+$calculations['quinceBimestral']; 
											break;

										case 3:
											$calculations['descuentoInfonavitTemp'] = (($calculations['sdi']*($calculations['descuentoEmpleado']/100)*$calculations['daysForImss']))+$calculations['quinceBimestral'];
											break;

										default:
											# code...
											break;
									}
								}
								else
								{
									$calculations['descuentoInfonavitTemp'] = 0 ;
								}

								// -------------------

								$calculations['fonacot']			=(($t_nominaemployee->workerData->first()->fonacot/30.4)*$calculations['daysForImss']);
								$calculations['loanRetention']		= $request->loan_retention[$i];
								$calculations['otherRetentionConcept']	= $request->other_retention_concept[$i];
								$calculations['otherRetentionAmount']	= $request->other_retention_amount[$i];

								$calculations['totalRetentionsTemp']	= round($calculations['imss'],2)+round($calculations['descuentoInfonavitTemp'],2)+round($calculations['fonacot'],2)+round($calculations['loanRetention'],2)+round($calculations['isrARetener'],2)+round($calculations['otherRetentionAmount'],2);

								$calculations['percentage']  	= ($calculations['totalRetentionsTemp'] * 100) / $calculations['salary'];

								if ($calculations['percentage']>80) 
								{
									$calculations['descuentoInfonavit']		= 0 ;
									$calculations['descuentoInfonavitComplemento'] = $calculations['descuentoInfonavitTemp'];

								}
								else
								{
									$calculations['descuentoInfonavit']		= $calculations['descuentoInfonavitTemp'];
									$calculations['descuentoInfonavitComplemento'] = 0;
								}

								$calculations['totalRetentions']	= round($calculations['imss'],2)+round($calculations['descuentoInfonavit'],2)+round($calculations['fonacot'],2)+round($calculations['loanRetention'],2)+round($calculations['isrARetener'],2)+round($calculations['otherRetentionAmount'],2);
								
								$calculations['netIncome']			= $calculations['totalPerceptions']-$calculations['totalRetentions'];

								//return $calculations;

								$arraySalary[$i]['fullname']			= $t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' '.$t_nominaemployee->employee->first()->name;
								$arraySalary[$i]['sd']					= $calculations['sd'];
								$arraySalary[$i]['sdi']					= $calculations['sdi'];
								$arraySalary[$i]['workedDays']			= $calculations['workedDays'];
								$arraySalary[$i]['periodicity']			= App\CatPeriodicity::find($request->periodicity[$i])->description;
								$arraySalary[$i]['range']				= $request->from_date[$i].' '.$request->to_date[$i];
								$arraySalary[$i]['salary']				= $calculations['salary'];
								$arraySalary[$i]['loan_perception']		= $calculations['loanPerception'];
								$arraySalary[$i]['puntuality']			= $calculations['puntuality'];
								$arraySalary[$i]['assistance']			= $calculations['assistance'];
								$arraySalary[$i]['subsidy']				= $calculations['subsidio'];
								$arraySalary[$i]['totalPerceptions']	= $calculations['totalPerceptions'];
								$arraySalary[$i]['imss']				= $calculations['imss'];
								$arraySalary[$i]['infonavit']			= $calculations['descuentoInfonavit'];
								$arraySalary[$i]['fonacot']				= $calculations['fonacot'];
								$arraySalary[$i]['loan_retention']		= $calculations['loanRetention'];
								$arraySalary[$i]['isrRetentions']		= $calculations['isrARetener'];
								$arraySalary[$i]['other_retention_amount'] = $calculations['otherRetentionAmount'];
								$arraySalary[$i]['other_retention_concept'] = $calculations['otherRetentionConcept'];
								$arraySalary[$i]['totalRetentions']		= $calculations['totalRetentions'];
								$arraySalary[$i]['netIncome']			= $calculations['netIncome'];
								$arraySalary[$i]['complement'] 			= round($request->netIncome[$i]-$calculations['netIncome']-$calculations['descuentoInfonavitComplemento'],2);
								$arraySalary[$i]['total'] 				= $request->netIncome[$i];
								$calculations	= [];
							}
							break;

						case '002':
							$arrayNomina = [];
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee				= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);

								$calculations = [];
								//calculo para dias de vacaciones
								$calculations['fechaIngreso']		= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$calculations['fechaActual']		= Carbon::now();
								$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
								$calculations['yearsWork']			= ceil($calculations['diasTrabajados']/365);
								if ($calculations['yearsWork'] > 24) 
								{
									$calculations['vacationDays']	= 20;
								}
								else
								{
									$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
								}

								//-------------------

								$calculations['prima_vac_esp']	= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
								$calculations['sdi']			= $t_nominaemployee->workerData->first()->sdi;
								$calculations['sd']				= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);
								$calculations['uma']							= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
								$calculations['exento']							= $calculations['uma']*30; 
								$calculations['diasParaAguinaldo']				= $request->day_bonus[$i];
								$calculations['parteProporcionalParaAguinaldo']	= round((15*$calculations['diasParaAguinaldo'])/365,6);


								// PERCEPCIONES AGUINALDO---------------------------------------------------------------------

								if (($calculations['parteProporcionalParaAguinaldo'] * $calculations['sd']) < $calculations['exento']) 
								{
									$calculations['aguinaldoExento'] = $calculations['parteProporcionalParaAguinaldo'] * $calculations['sd'];
								}
								else
								{
									$calculations['aguinaldoExento'] = $calculations['exento'];
								}

								if (($calculations['parteProporcionalParaAguinaldo'] * $calculations['sd']) > $calculations['exento']) 
								{
									$calculations['aguinaldoGravable'] = ($calculations['parteProporcionalParaAguinaldo'] * $calculations['sd'])-$calculations['aguinaldoExento'];
								}
								else
								{
									$calculations['aguinaldoGravable'] = 0;
								}

								$calculations['totalPercepciones'] = round($calculations['aguinaldoExento'],2) + round($calculations['aguinaldoGravable'],2);

								// --------------------------------------------------------------------------------------------

								// RETENCIONES- ISR ---------------------------------------------------------------------

								// ISR 1ER FRACCION

								$calculations['baseISR_fraccion1']			= round((($calculations['aguinaldoGravable']/365)*30.4)+($calculations['sd']*30),6);
								$parameterISRF1								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion1'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion1']	= $parameterISRF1->last()->inferior;
								$calculations['excedente_fraccion1']		= round($calculations['baseISR_fraccion1']-$calculations['limiteInferior_fraccion1'],6);
								$calculations['factor_fraccion1']			= round($parameterISRF1->last()->excess/100,6);
								$calculations['isrMarginal_fraccion1']		= round($calculations['excedente_fraccion1'] * $calculations['factor_fraccion1'],6);
								$calculations['cuotaFija_fraccion1']		= round($parameterISRF1->last()->quota,6);
								$calculations['isr_fraccion1']				= round($calculations['isrMarginal_fraccion1']+$calculations['cuotaFija_fraccion1'],6);

								// ISR 2DA FRACCION

								$calculations['baseISR_fraccion2']			= round($calculations['sd']*30,6);
								$parameterISRF2								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion2'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion2']	= $parameterISRF2->last()->inferior;
								$calculations['excedente_fraccion2']		= round($calculations['baseISR_fraccion2']-$calculations['limiteInferior_fraccion2'],6);
								$calculations['factor_fraccion2']			= round($parameterISRF2->last()->excess/100,6);
								$calculations['isrMarginal_fraccion2']		= round($calculations['excedente_fraccion2'] * $calculations['factor_fraccion2'],6);
								$calculations['cuotaFija_fraccion2']		= round($parameterISRF2->last()->quota,6);
								$calculations['isr_fraccion2']				= round($calculations['isrMarginal_fraccion2']+$calculations['cuotaFija_fraccion2'],6);

								$calculations['resta']		= round($calculations['isr_fraccion1']-$calculations['isr_fraccion2'],6);
								$calculations['factor1']	= round((($calculations['aguinaldoGravable']/365) * 30.4),6);
								if($calculations['factor1'] == 0)
								{
									$calculations['factor2']	= 0;
								}
								else
								{
									$calculations['factor2']	= round($calculations['resta']/$calculations['factor1'],6);
								}
								$calculations['isr']		= round($calculations['factor2']*$calculations['aguinaldoGravable'],6);

								$calculations['totalRetenciones'] = round($calculations['isr'],2);

								// --------------------------------------------------------------------------------------------

								
								$calculations['netIncome']			= $calculations['totalPercepciones']-$calculations['totalRetenciones'];

								$arrayBonus[$i]['fullname']							= $t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' '.$t_nominaemployee->employee->first()->name;
								$arrayBonus[$i]['sd']								= $calculations['sd'];
								$arrayBonus[$i]['sdi']								= $calculations['sdi'];
								$arrayBonus[$i]['fechaIngreso']						= $calculations['fechaIngreso'];
								$arrayBonus[$i]['diasParaAguinaldo']				= $calculations['diasParaAguinaldo'];
								$arrayBonus[$i]['parteProporcionalParaAguinaldo']	= $calculations['parteProporcionalParaAguinaldo'];
								$arrayBonus[$i]['aguinaldoExento']					= $calculations['aguinaldoExento'];
								$arrayBonus[$i]['aguinaldoGravable']				= $calculations['aguinaldoGravable'];
								$arrayBonus[$i]['totalPercepciones']				= $calculations['totalPercepciones'];
								$arrayBonus[$i]['isr']								= $calculations['isr'];
								$arrayBonus[$i]['totalRetenciones']					= $calculations['totalRetenciones'];
								$arrayBonus[$i]['netIncome']						= $calculations['netIncome'];

								$calculations = [];

							}
							
							break;

						case '003':
						case '004':
							$arrayNomina = [];
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee					= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);

								// ----- calculo para dias de vacaciones ---------------------------
								$calculations					= [];

								$calculations['fechaIngreso']	= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$calculations['fechaBaja']		= $request->down_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->down_date[$i])->format('Y-m-d') : null;
								
								$calculations['fechaActual']	= Carbon::now();
								$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
								$calculations['añosTrabajados']	= ceil($calculations['diasTrabajados']/365);

								$calculations['diasTrabajadosParaAñosCompletos'] = App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaBaja']);

								$calculations['añosCompletos']	= floor($calculations['diasTrabajadosParaAñosCompletos']/365);
								if ($calculations['añosTrabajados'] > 24) 
								{
									$calculations['diasDeVacaciones']	= 20;
								}
								else
								{
									$calculations['diasDeVacaciones']	= App\ParameterVacation::where('fromYear','<=',$calculations['añosTrabajados'])->where('toYear','>=',$calculations['añosTrabajados'])->first()->days;
								}

								//------------------------------------------------------------------
								
								$calculations['prima_vac_esp']		= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
								$calculations['sdi']				= $t_nominaemployee->workerData->first()->sdi;
								$calculations['sd']					= round($calculations['sdi']/((($calculations['diasDeVacaciones']*$calculations['prima_vac_esp'])+15+365)/365),2);
								
								$calculations['diasTrabajadosM']	= $request->worked_days[$i];
								
								$calculations['diasParaVacaciones']	= ($calculations['diasDeVacaciones']*$calculations['diasTrabajadosM'])/365;
								//dias trabajados para aguinaldo va del 1 de enero a la fecha de baja
								$date1 = new \DateTime(date("Y").'-01-01');
								$date2 = new \DateTime($calculations['fechaIngreso']);
								if ($date2 > $date1) 
								{
									$fechaParaDiasAguinaldo = $calculations['fechaIngreso'];
								}
								else
								{
									$fechaParaDiasAguinaldo = date("Y").'-01-01';
								}
								$calculations['diasTrabajadosParaAguinaldo'] = App\Http\Controllers\AdministracionNominaController::daysPassed($fechaParaDiasAguinaldo,$calculations['fechaBaja'])+1;

								$calculations['diasParaAguinaldo'] 	= ($calculations['diasTrabajadosParaAguinaldo']*15)/365;

								if ($request->type_payroll == '004') 
								{
									$calculations['sueldoPorLiquidacion']		= round($calculations['sd']*90,6);
									$calculations['veinteDiasPorAñoServicio']	= round(20*$calculations['añosCompletos']*$calculations['sd'],6);
									
									// VARIABLES -------------------------------------------------------
									$calculations['uma']							= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
									$calculations['salarioMinimo']					= App\Parameter::where('parameter_name','SALARY_VDF')->first()->parameter_value; 
									$calculations['valorPrimaAntiguedad']			= $calculations['salarioMinimo']*2;
									$calculations['exento']							= $calculations['uma']*90; 
									$calculations['valorAguinaldoExento']			= $calculations['uma']*30; 
									$calculations['valorPrimaVacacaionalExenta']	= $calculations['uma']*15; 
									$calculations['valorIndemnizacionExenta']		= $calculations['uma']*90;
									//  PRIMA DE ANTIGUEDAD ------------------------------------------------------------------

									if ($calculations['sd']>=$calculations['valorPrimaAntiguedad']) 
									{
										$calculations['primaAntiguedad'] = round($calculations['añosCompletos']*12*$calculations['valorPrimaAntiguedad'],6);
									}
									else
									{
										$calculations['primaAntiguedad'] = round($calculations['añosCompletos']*12*$calculations['sd'],6);
									}

									//  INDEMNIZACION ------------------------------------------------------------------
									$calculations['indemnizacion'] =  round($calculations['sueldoPorLiquidacion']+$calculations['veinteDiasPorAñoServicio']+$calculations['primaAntiguedad'],6);

									if ($calculations['indemnizacion'] < $calculations['valorIndemnizacionExenta']) 
									{
										$calculations['indemnizacionExcenta']	= $calculations['indemnizacion'];
									}
									else
									{
										$calculations['indemnizacionExcenta']	= $calculations['valorIndemnizacionExenta'];
									}


									if ($calculations['indemnizacion'] > $calculations['valorIndemnizacionExenta']) 
									{
										$calculations['indemnizacionGravada']	= $calculations['indemnizacion']-$calculations['indemnizacionExcenta'];
									}
									else
									{
										$calculations['indemnizacionGravada']	= 0;
									}

									$calculations['vacaciones']				= $calculations['diasParaVacaciones']*$calculations['sd'];


									// PERCEPCIONES AGUINALDO---------------------------------------------------------------------

									if (($calculations['diasParaAguinaldo'] * $calculations['sd']) < $calculations['valorAguinaldoExento']) 
									{
										$calculations['aguinaldoExento'] = $calculations['diasParaAguinaldo'] * $calculations['sd'];
									}
									else
									{
										$calculations['aguinaldoExento'] = $calculations['valorAguinaldoExento'];
									}

									if (($calculations['diasParaAguinaldo'] * $calculations['sd']) > $calculations['valorAguinaldoExento']) 
									{
										$calculations['aguinaldoGravable'] = ($calculations['diasParaAguinaldo'] * $calculations['sd'])-$calculations['aguinaldoExento'];
									}
									else
									{
										$calculations['aguinaldoGravable'] = 0;
									}


									//-------- PERCEPCIONES ---------------------------------------------------------------


									if (($calculations['vacaciones']*$calculations['prima_vac_esp'])<$calculations['valorPrimaVacacaionalExenta'])
									{
										$calculations['primaVacacionalExenta'] = round($calculations['vacaciones']*$calculations['prima_vac_esp'],6);
									}
									else
									{
										$calculations['primaVacacionalExenta'] = $calculations['valorPrimaVacacaionalExenta'];
									}

									if (($calculations['vacaciones']*$calculations['prima_vac_esp'])>$calculations['valorPrimaVacacaionalExenta'])
									{
										$calculations['primaVacacionalGravada'] = round(($calculations['vacaciones']*$calculations['prima_vac_esp'])-$calculations['primaVacacionalExenta'],6);
									}
									else
									{
										$calculations['primaVacacionalGravada'] = 0;
									}

									$calculations['otrasPercepciones'] = $request->other_perception[$i];

									$calculations['totalPercepciones'] = round($calculations['sueldoPorLiquidacion'],2)+round($calculations['veinteDiasPorAñoServicio'],2)+round($calculations['primaAntiguedad'],2)+round($calculations['vacaciones'],2)+round($calculations['aguinaldoExento'],2)+round($calculations['aguinaldoGravable'],2)+round($calculations['primaVacacionalExenta'],2)+round($calculations['primaVacacionalGravada'],2)+round($calculations['otrasPercepciones'],2);
								}
								else
								{
									// VARIABLES -------------------------------------------------------
									$calculations['uma']							= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
									$calculations['salarioMinimo']					= App\Parameter::where('parameter_name','SALARY_VDF')->first()->parameter_value; 
									$calculations['valorPrimaAntiguedad']			= $calculations['salarioMinimo']*2;
									$calculations['exento']							= $calculations['uma']*90; 
									$calculations['valorAguinaldoExento']			= $calculations['uma']*30; 
									$calculations['valorPrimaVacacaionalExenta']	= $calculations['uma']*15; 
									$calculations['valorIndemnizacionExenta']		= $calculations['uma']*90;
									//  PRIMA DE ANTIGUEDAD ------------------------------------------------------------------

									if ($calculations['sd']>=$calculations['valorPrimaAntiguedad']) 
									{
										$calculations['primaAntiguedad'] = round($calculations['añosCompletos']*12*$calculations['valorPrimaAntiguedad'],6);
									}
									else
									{
										$calculations['primaAntiguedad'] = round($calculations['añosCompletos']*12*$calculations['sd'],6);
									}

									//  INDEMNIZACION  ------------------------------------------------------------------

									if ($calculations['primaAntiguedad'] < $calculations['valorIndemnizacionExenta']) 
									{
										$calculations['indemnizacionExcenta']	= $calculations['primaAntiguedad'];
									}
									else
									{
										$calculations['indemnizacionExcenta']	= $calculations['valorIndemnizacionExenta'];
									}


									if ($calculations['primaAntiguedad'] > $calculations['valorIndemnizacionExenta']) 
									{
										$calculations['indemnizacionGravada']	= $calculations['primaAntiguedad']-$calculations['indemnizacionExcenta'];
									}
									else
									{
										$calculations['indemnizacionGravada']	= 0;
									}

									$calculations['vacaciones']				= $calculations['diasParaVacaciones']*$calculations['sd'];


									// PERCEPCIONES AGUINALDO---------------------------------------------------------------------

									if (($calculations['diasParaAguinaldo'] * $calculations['sd']) < $calculations['valorAguinaldoExento']) 
									{
										$calculations['aguinaldoExento'] = $calculations['diasParaAguinaldo'] * $calculations['sd'];
									}
									else
									{
										$calculations['aguinaldoExento'] = $calculations['valorAguinaldoExento'];
									}

									if (($calculations['diasParaAguinaldo'] * $calculations['sd']) > $calculations['valorAguinaldoExento']) 
									{
										$calculations['aguinaldoGravable'] = ($calculations['diasParaAguinaldo'] * $calculations['sd'])-$calculations['aguinaldoExento'];
									}
									else
									{
										$calculations['aguinaldoGravable'] = 0;
									}


									//-------- PERCEPCIONES PRIMA VACACIONAL ---------------------------------------------------------------


									if (($calculations['vacaciones']*$calculations['prima_vac_esp'])<$calculations['valorPrimaVacacaionalExenta'])
									{
										$calculations['primaVacacionalExenta'] = round($calculations['vacaciones']*$calculations['prima_vac_esp'],6);
									}
									else
									{
										$calculations['primaVacacionalExenta'] = $calculations['valorPrimaVacacaionalExenta'];
									}

									if (($calculations['vacaciones']*$calculations['prima_vac_esp'])>$calculations['valorPrimaVacacaionalExenta'])
									{
										$calculations['primaVacacionalGravada'] = round(($calculations['vacaciones']*$calculations['prima_vac_esp'])-$calculations['primaVacacionalExenta'],6);
									}
									else
									{
										$calculations['primaVacacionalGravada'] = 0;
									}

									$calculations['otrasPercepciones'] = $request->other_perception[$i];

									$calculations['totalPercepciones'] = round($calculations['primaAntiguedad'],2)+round($calculations['vacaciones'],2)+round($calculations['aguinaldoExento'],2)+round($calculations['aguinaldoGravable'],2)+round($calculations['primaVacacionalExenta'],2)+round($calculations['primaVacacionalGravada'],2)+round($calculations['otrasPercepciones'],2);

									// ------------------------------------------------------------------------------------
								}

								//-------- RETENCIONES ----------------------------------------------------------------

								// ISR 1ER FRACCION

								$calculations['baseISR_fraccion1']			= round(((($calculations['vacaciones']+$calculations['aguinaldoGravable']+$calculations['primaVacacionalGravada'])/365)*30.4)+($calculations['sd']*30),6);
								$parameterISRF1								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion1'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion1']	= $parameterISRF1->last()->inferior;
								$calculations['excedente_fraccion1']		= round($calculations['baseISR_fraccion1']-$calculations['limiteInferior_fraccion1'],6);
								$calculations['factor_fraccion1']			= round($parameterISRF1->last()->excess/100,6);
								$calculations['isrMarginal_fraccion1']		= round($calculations['excedente_fraccion1'] * $calculations['factor_fraccion1'],6);
								$calculations['cuotaFija_fraccion1']		= round($parameterISRF1->last()->quota,6);
								$calculations['isr_fraccion1']				= round($calculations['isrMarginal_fraccion1']+$calculations['cuotaFija_fraccion1'],6);

								// ISR 2DA FRACCION

								$calculations['baseISR_fraccion2']			= round($calculations['sd']*30,6);
								$parameterISRF2								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion2'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion2']	= $parameterISRF2->last()->inferior;
								$calculations['excedente_fraccion2']		= round($calculations['baseISR_fraccion2']-$calculations['limiteInferior_fraccion2'],6);
								$calculations['factor_fraccion2']			= round($parameterISRF2->last()->excess/100,6);
								$calculations['isrMarginal_fraccion2']		= round($calculations['excedente_fraccion2'] * $calculations['factor_fraccion2'],6);
								$calculations['cuotaFija_fraccion2']		= round($parameterISRF2->last()->quota,6);
								$calculations['isr_fraccion2']				= round($calculations['isrMarginal_fraccion2']+$calculations['cuotaFija_fraccion2'],6);

								$calculations['resta']		= round($calculations['isr_fraccion1']-$calculations['isr_fraccion2'],6);
								if ($calculations['resta'] == 0) 
								{
									$calculations['factor1']	= 0;
									$calculations['factor2']	= 0;
									$calculations['isr']		= 0;
								}
								else
								{
									$calculations['factor1']	= round(((($calculations['vacaciones']+$calculations['aguinaldoGravable']+$calculations['primaVacacionalGravada'])/365)*30.4),6);
									$calculations['factor2']	= round($calculations['resta']/$calculations['factor1'],6);
									$calculations['isr']		= round($calculations['factor2']*($calculations['vacaciones']+$calculations['aguinaldoGravable']+$calculations['primaVacacionalGravada']),6);
								}

								// ISR FINIQUITO (INDEMNIZACION)

								$calculations['baseTotalDePercepciones']	= round($calculations['sd']*30,6);
								$calculations['baseISR_finiquito']			= $calculations['baseTotalDePercepciones'];
								
								$parameterISRFiniquito						= App\ParameterISR::where('inferior','<=',$calculations['baseISR_finiquito'])->where('lapse',30)->get();
								
								$calculations['limiteInferior_finiquito']	= $parameterISRFiniquito->last()->inferior;
								$calculations['excedente_finiquito']		= round($calculations['baseISR_finiquito']-$calculations['limiteInferior_finiquito'],6);
								$calculations['factor_finiquito']			= round($parameterISRFiniquito->last()->excess/100,6);
								$calculations['isrMarginal_finiquito']		= round($calculations['excedente_finiquito'] * $calculations['factor_finiquito'],6);
								$calculations['cuotaFija_finiquito']		= round($parameterISRFiniquito->last()->quota,6);
								$calculations['isr_salario']				= round($calculations['isrMarginal_finiquito']+$calculations['cuotaFija_finiquito'],6);
								
								$calculations['isr_finiquito']				= round(($calculations['isr_salario']/$calculations['baseTotalDePercepciones'])*$calculations['indemnizacionGravada'],6);
								
								$calculations['totalISR']					= $calculations['isr_finiquito'] + $calculations['isr']; 
								$calculations['totalRetenciones']			= round($calculations['totalISR'],2);

								// --------------------------------------------------------------------------------------------

								
								$calculations['netIncome']			= $calculations['totalPercepciones']-$calculations['totalRetenciones'];

								$arrayLiquidation[$i]['fullname']			= $t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' '.$t_nominaemployee->employee->first()->name;
								$arrayLiquidation[$i]['sd']					= $calculations['sd'];
								$arrayLiquidation[$i]['sdi']				= $calculations['sdi'];
								$arrayLiquidation[$i]['fechaIngreso']		= $calculations['fechaIngreso'];
								$arrayLiquidation[$i]['fechaBaja']			= $calculations['fechaBaja'];
								$arrayLiquidation[$i]['añosCompletos']		= $calculations['añosCompletos'];
								$arrayLiquidation[$i]['diasTrabajadosM']	= $calculations['diasTrabajadosM'];
								$arrayLiquidation[$i]['diasParaVacaciones']	= $calculations['diasParaVacaciones'];
								$arrayLiquidation[$i]['diasParaAguinaldo']	= $calculations['diasParaAguinaldo'];
								if ($request->type_payroll == '004') 
								{
									$arrayLiquidation[$i]['sueldoPorLiquidacion']			= $calculations['sueldoPorLiquidacion'];
									$arrayLiquidation[$i]['veinteDiasPorAñoServicio']	= $calculations['veinteDiasPorAñoServicio'];
								}
								$arrayLiquidation[$i]['primaAntiguedad']		= $calculations['primaAntiguedad'];
								$arrayLiquidation[$i]['indemnizacionExcenta']	= $calculations['indemnizacionExcenta'];
								$arrayLiquidation[$i]['indemnizacionGravada']	= $calculations['indemnizacionGravada'];
								$arrayLiquidation[$i]['vacaciones']				= $calculations['vacaciones'];
								$arrayLiquidation[$i]['aguinaldoExento']		= $calculations['aguinaldoExento'];
								$arrayLiquidation[$i]['aguinaldoGravable']		= $calculations['aguinaldoGravable'];
								$arrayLiquidation[$i]['primaVacacionalExenta']	= $calculations['primaVacacionalExenta'];
								$arrayLiquidation[$i]['primaVacacionalGravada']	= $calculations['primaVacacionalGravada'];
								$arrayLiquidation[$i]['otrasPercepciones']		= $calculations['otrasPercepciones'];
								$arrayLiquidation[$i]['totalPercepciones']		= $calculations['totalPercepciones'];
								$arrayLiquidation[$i]['totalISR']				= $calculations['totalISR'];
								$arrayLiquidation[$i]['totalRetenciones']		= $calculations['totalRetenciones'];
								$arrayLiquidation[$i]['netIncome']				= $calculations['netIncome'];

								$calculations = [];

							}

							break;

						case '005':
							$arrayNomina = [];
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee				= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								

								// ----- calculo para dias de vacaciones ---------------------------
								$calculations					= [];
								$calculations['fechaIngreso']	= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$calculations['fechaActual']	= Carbon::now();
								$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
								$calculations['yearsWork']		= ceil($calculations['diasTrabajados']/365);
								if ($calculations['yearsWork'] > 24) 
								{
									$calculations['vacationDays']	= 20;
								}
								else
								{
									$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
								}

								//------------------------------------------------------------------
								
								$calculations['prima_vac_esp']		= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
								$calculations['sdi']				= $t_nominaemployee->workerData->first()->sdi;
								$calculations['sd']					= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);
								
								$calculations['diasTrabajadosM']	= $request->worked_days[$i];
								
								$calculations['diasParaVacaciones']	= ($calculations['vacationDays']*$calculations['diasTrabajadosM'])/365;
								
								$calculations['uma']				= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
								$calculations['exento']				= $calculations['uma']*15; 

								//-------- PERCEPCIONES ---------------------------------------------------------------

								$calculations['vacaciones'] = $calculations['sd']*$calculations['diasParaVacaciones'];

								if (($calculations['sd']*$calculations['diasParaVacaciones']*$calculations['prima_vac_esp'])<$calculations['exento'])
								{
									$calculations['primaVacacionalExenta'] = round($calculations['sd']*$calculations['diasParaVacaciones']*$calculations['prima_vac_esp'],6);
								}
								else
								{
									$calculations['primaVacacionalExenta'] = $calculations['exento'];
								}

								if (($calculations['sd']*$calculations['diasParaVacaciones']*$calculations['prima_vac_esp'])>$calculations['exento'])
								{
									$calculations['primaVacacionalGravada'] = round(($calculations['sd']*$calculations['diasParaVacaciones']*$calculations['prima_vac_esp'])-$calculations['primaVacacionalExenta'],6);
								}
								else
								{
									$calculations['primaVacacionalGravada'] = 0;
								}

								$calculations['totalPercepciones'] = round($calculations['primaVacacionalExenta'],2)+round($calculations['primaVacacionalGravada'],2);

								// ------------------------------------------------------------------------------------

								//-------- RETENCIONES ----------------------------------------------------------------

								// ISR 1ER FRACCION

								$calculations['baseISR_fraccion1']			= round((($calculations['primaVacacionalGravada']/365)*30.4)+($calculations['sd']*30),6);
								$parameterISRF1								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion1'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion1']	= $parameterISRF1->last()->inferior;
								$calculations['excedente_fraccion1']		= round($calculations['baseISR_fraccion1']-$calculations['limiteInferior_fraccion1'],6);
								$calculations['factor_fraccion1']			= round($parameterISRF1->last()->excess/100,6);
								$calculations['isrMarginal_fraccion1']		= round($calculations['excedente_fraccion1'] * $calculations['factor_fraccion1'],6);
								$calculations['cuotaFija_fraccion1']		= round($parameterISRF1->last()->quota,6);
								$calculations['isr_fraccion1']				= round($calculations['isrMarginal_fraccion1']+$calculations['cuotaFija_fraccion1'],6);

								// ISR 2DA FRACCION

								$calculations['baseISR_fraccion2']			= round($calculations['sd']*30,6);
								$parameterISRF2								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion2'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion2']	= $parameterISRF2->last()->inferior;
								$calculations['excedente_fraccion2']		= round($calculations['baseISR_fraccion2']-$calculations['limiteInferior_fraccion2'],6);
								$calculations['factor_fraccion2']			= round($parameterISRF2->last()->excess/100,6);
								$calculations['isrMarginal_fraccion2']		= round($calculations['excedente_fraccion2'] * $calculations['factor_fraccion2'],6);
								$calculations['cuotaFija_fraccion2']		= round($parameterISRF2->last()->quota,6);
								$calculations['isr_fraccion2']				= round($calculations['isrMarginal_fraccion2']+$calculations['cuotaFija_fraccion2'],6);

								$calculations['resta']		= round($calculations['isr_fraccion1']-$calculations['isr_fraccion2'],6);
								if ($calculations['resta'] == 0) 
								{
									$calculations['factor1']	= 0;
									$calculations['factor2']	= 0;
									$calculations['isr']		= 0;
								}
								else
								{
									$calculations['factor1']	= round((($calculations['primaVacacionalGravada']/365) * 30.4),6);
									$calculations['factor2']	= round($calculations['resta']/$calculations['factor1'],6);
									$calculations['isr']		= round($calculations['factor2']*$calculations['primaVacacionalGravada'],6);
								}

								$calculations['totalRetenciones'] = round($calculations['isr'],2);

								// --------------------------------------------------------------------------------------------

								
								$calculations['netIncome']			= $calculations['totalPercepciones']-$calculations['totalRetenciones'];

								$arrayVP[$i]['fullname']				= $t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' '.$t_nominaemployee->employee->first()->name;
								$arrayVP[$i]['sd']						= $calculations['sd'];
								$arrayVP[$i]['sdi']						= $calculations['sdi'];
								$arrayVP[$i]['dateOfAdmission']			= $calculations['fechaIngreso'];
								$arrayVP[$i]['workedDays']				= $calculations['diasTrabajadosM'];
								$arrayVP[$i]['holidaysDays']			= $calculations['diasParaVacaciones'];
								$arrayVP[$i]['holidays']				= $calculations['vacaciones'];
								$arrayVP[$i]['exemptHolidayPremium']	= $calculations['primaVacacionalExenta'];
								$arrayVP[$i]['holidayPremiumTaxed']		= $calculations['primaVacacionalGravada'];
								$arrayVP[$i]['totalPerceptions']		= $calculations['totalPercepciones'];
								$arrayVP[$i]['isr']						= $calculations['isr'];
								$arrayVP[$i]['totalTaxes']				= $calculations['totalRetenciones'];
								$arrayVP[$i]['netIncome']				= $calculations['netIncome'];
								$calculations = [];

							}
						
							break;

						case '006':
							$arrayNomina			= [];
							$t_nomina			= App\Nomina::find($t_request->nominasReal->first()->idnomina);
							$sumaDiasTrabajados	= 0;
							$sumaSueldoTotal	= 0;
							//------- calculo para sumatoria de dias trabajados y sueldo total ------------------------
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{
								$t_nominaemployee				= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								
								$sumaDiasTrabajados		 		+= $request->worked_days[$i];
								$calculations					= [];
								$calculations['fechaIngreso']	= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$calculations['fechaActual']	= Carbon::now();
								$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
								$calculations['yearsWork']		= ceil($calculations['diasTrabajados']/365);
								if ($calculations['yearsWork'] > 24) 
								{
									$calculations['vacationDays']	= 20;
								}
								else
								{
									$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
								}


								$calculations['prima_vac_esp']	= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
								$calculations['sdi']			= $t_nominaemployee->workerData->first()->sdi;
								$calculations['sd']				= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);

								$sumaSueldoTotal += round($request->worked_days[$i] * $calculations['sd'],6);
								$calculations = [];
							}

							// -------------------------------------------------------------------------------------------------------
							for ($i=0; $i < count($request->idnominaEmployee_request); $i++) 
							{ 
								$t_nominaemployee				= App\NominaEmployee::find($request->idnominaEmployee_request[$i]);
								$t_nominaemployee->worked_days	= $request->worked_days[$i];
								$t_nominaemployee->save();

								// ----- calculo para dias de vacaciones ---------------------------
								$calculations					= [];
								$calculations['fechaIngreso']	= $t_nominaemployee->workerData->first()->imssDate->format('Y-m-d');
								$calculations['fechaActual']	= Carbon::now();
								$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
								$calculations['yearsWork']		= ceil($calculations['diasTrabajados']/365);
								if ($calculations['yearsWork'] > 24) 
								{
									$calculations['vacationDays']	= 20;
								}
								else
								{
									$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
								}

								//------------------------------------------------------------------
								
								$calculations['prima_vac_esp']		= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
								$calculations['sdi']				= $t_nominaemployee->workerData->first()->sdi;
								$calculations['sd']					= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);
								
								$calculations['diasTrabajadosM']	= $request->worked_days[$i];
								$calculations['sueldoTotal']		= round($calculations['diasTrabajadosM'] * $calculations['sd'],6);
								
								$calculations['sumaDiasTrabajados']	= $sumaDiasTrabajados;
								$calculations['sumaSueldoTotal']	= $sumaSueldoTotal;

								$calculations['uma']							= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
								$calculations['exento']							= $calculations['uma']*15; 

								$calculations['ptuPorPagar']		= round($request->ptu_to_pay,6);
								$calculations['factorPorDias']		= round(($calculations['ptuPorPagar']/2)/$calculations['sumaDiasTrabajados'],6);
								$calculations['factorPorSueldo']	= round(($calculations['ptuPorPagar']/2)/$calculations['sumaSueldoTotal'],6);

								$calculations['ptuPorDias']		= round($calculations['diasTrabajadosM'] * $calculations['factorPorDias'],6);
								$calculations['ptuPorSueldos']	= round($calculations['sueldoTotal']*$calculations['factorPorSueldo'],6);
								$calculations['ptuTotal']		= round($calculations['ptuPorDias']+$calculations['ptuPorSueldos'],6);

								//-------- PERCEPCIOONES -------------------------------------------------------------

								$calculations['ptuExenta']			= $calculations['exento'];
								$calculations['ptuGravada']			= round($calculations['ptuTotal']-$calculations['ptuExenta'],6);
								$calculations['totalPercepciones']	= round($calculations['ptuExenta'],2)+round($calculations['ptuGravada'],2);


								// ------------------------------------------------------------------------------------

								//-------- RETENCIONES ----------------------------------------------------------------

								// ISR 1ER FRACCION

								$calculations['baseISR_fraccion1']			= round((($calculations['ptuGravada']/365)*30.4)+($calculations['sd']*30),6);
								$parameterISRF1								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion1'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion1']	= $parameterISRF1->last()->inferior;
								$calculations['excedente_fraccion1']		= round($calculations['baseISR_fraccion1']-$calculations['limiteInferior_fraccion1'],6);
								$calculations['factor_fraccion1']			= round($parameterISRF1->last()->excess/100,6);
								$calculations['isrMarginal_fraccion1']		= round($calculations['excedente_fraccion1'] * $calculations['factor_fraccion1'],6);
								$calculations['cuotaFija_fraccion1']		= round($parameterISRF1->last()->quota,6);
								$calculations['isr_fraccion1']				= round($calculations['isrMarginal_fraccion1']+$calculations['cuotaFija_fraccion1'],6);

								// ISR 2DA FRACCION

								$calculations['baseISR_fraccion2']			= round($calculations['sd']*30,6);
								$parameterISRF2								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion2'])->where('lapse',30)->get();

								$calculations['limiteInferior_fraccion2']	= $parameterISRF2->last()->inferior;
								$calculations['excedente_fraccion2']		= round($calculations['baseISR_fraccion2']-$calculations['limiteInferior_fraccion2'],6);
								$calculations['factor_fraccion2']			= round($parameterISRF2->last()->excess/100,6);
								$calculations['isrMarginal_fraccion2']		= round($calculations['excedente_fraccion2'] * $calculations['factor_fraccion2'],6);
								$calculations['cuotaFija_fraccion2']		= round($parameterISRF2->last()->quota,6);
								$calculations['isr_fraccion2']				= round($calculations['isrMarginal_fraccion2']+$calculations['cuotaFija_fraccion2'],6);

								$calculations['resta']		= round($calculations['isr_fraccion1']-$calculations['isr_fraccion2'],6);
								$calculations['factor1']	= round((($calculations['ptuGravada']/365) * 30.4),6);
								if($calculations['factor1'] == 0)
								{
									$calculations['factor2']	= 0;
								}
								else
								{
									$calculations['factor2']	= round($calculations['resta']/$calculations['factor1'],6);
								}

								$calculations['isr']		= round($calculations['factor2']*$calculations['ptuGravada'],6);

								$calculations['totalRetenciones'] = round($calculations['isr'],2);

								// --------------------------------------------------------------------------------------------

								
								$calculations['netIncome']			= $calculations['totalPercepciones']-$calculations['totalRetenciones'];

								$arrayPS[$i]['fullname']			= $t_nominaemployee->employee->first()->last_name.' '.$t_nominaemployee->employee->first()->scnd_last_name.' '.$t_nominaemployee->employee->first()->name;
								$arrayPS[$i]['fechaIngreso'] 		= $calculations['fechaIngreso'];
								$arrayPS[$i]['sd']					= $calculations['sd'];
								$arrayPS[$i]['sdi']					= $calculations['sdi'];
								$arrayPS[$i]['diasTrabajadosM']		= $calculations['diasTrabajadosM'];
								$arrayPS[$i]['sueldoTotal']			= $calculations['sueldoTotal'];
								$arrayPS[$i]['ptuPorDias']			= $calculations['ptuPorDias'];
								$arrayPS[$i]['ptuPorSueldos']		= $calculations['ptuPorSueldos'];
								$arrayPS[$i]['ptuTotal']			= $calculations['ptuTotal'];
								$arrayPS[$i]['ptuExenta']			= $calculations['ptuExenta'];
								$arrayPS[$i]['ptuGravada']			= $calculations['ptuGravada'];
								$arrayPS[$i]['totalPercepciones']	= $calculations['totalPercepciones'];
								$arrayPS[$i]['isr']					= $calculations['isr'];
								$arrayPS[$i]['totalRetenciones']	= $calculations['totalRetenciones'];
								$arrayPS[$i]['netIncome']			= $calculations['netIncome'];

								$calculations = [];

							}
							break;

						
						default:
							# code...
							break;
					}
				}
				else
				{
					for ($i=0; $i < count($request->request_idnominaEmployee); $i++) 
					{ 
						$t_nominaemployee						= App\NominaEmployee::find($request->request_idnominaEmployee[$i]);
						$arrayNomina[$i]['curp']				= $t_nominaemployee->employee->first()->curp;
						$arrayNomina[$i]['empresa']				= $t_nominaemployee->workerData->first()->enterprises->name;
						$arrayNomina[$i]['proyecto']			= $t_nominaemployee->workerData->first()->projects()->exists() ? $t_nominaemployee->workerData->first()->projects->proyectName : "Sin Proyecto";
						$arrayNomina[$i]['nombre']				= $t_nominaemployee->employee->first()->name;
						$arrayNomina[$i]['apellido_paterno']	= $t_nominaemployee->employee->first()->last_name;
						$arrayNomina[$i]['apellido_materno']	= $t_nominaemployee->employee->first()->scnd_last_name;
						$arrayNomina[$i]['monto_total']			= round($request->request_netIncome[$i],2);
						$arrayNomina[$i]['monto_fiscal']		= 0;
						$arrayNomina[$i]['complemento']			= round($request->request_complement[$i],2);
					}
				}

				if ($arraySalary != null) 
				{
					$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
					$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
					$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('315864')->setFontColor(Color::WHITE)->setFontBold()->build();
					$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('7C9248')->setFontColor(Color::WHITE)->setFontBold()->build();
					$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('8B3C38')->setFontColor(Color::WHITE)->setFontBold()->build();
					$mhStyleCol4	= (new StyleBuilder())->setBackgroundColor('618BCF')->setFontColor(Color::WHITE)->setFontBold()->build();
					$mhStyleCol5	= (new StyleBuilder())->setBackgroundColor('5C4A77')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol1	= (new StyleBuilder())->setBackgroundColor('9ECBDA')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol2	= (new StyleBuilder())->setBackgroundColor('C8D5A1')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol3	= (new StyleBuilder())->setBackgroundColor('D09996')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol4	= (new StyleBuilder())->setBackgroundColor('C9D8EF')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol5	= (new StyleBuilder())->setBackgroundColor('AEA1C4')->setFontColor(Color::WHITE)->setFontBold()->build();
					$writer			= WriterEntityFactory::createXLSXWriter();
					$writer->setDefaultRowStyle($defaultStyle)->openToBrowser($request->title.'.xlsx');

					$writer->getCurrentSheet()->setName('Salario');
					$headerArray	= ['INFORMACIÓN','','','','','','PERCEPCIONES','','','','','','RETENCIONES','','','','','','',''];
					$tempHeaders		= [];
					foreach($headerArray as $k => $header)
					{
						if($k <= 5)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
						}
						elseif($k <= 11)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
						}
						elseif($k <= 18)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
						}
						else
						{
							$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
						}
					}
					$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
					$writer->addRow($rowFromValues);

					$subHeaderArray	= ['empleado','sd','sdi','dias_trabajados','periodicidad','rango_de_fechas','sueldo','prestamo_percepcion','puntualidad','asistencia','subsidio','total_de_percepciones','imss','infonavit','fonacot','prestamo_retencion','retencion_de_isr','otra_retencion_concepto','otra_retencion_importe','total_de_retenciones', 'sueldo_neto_fiscal', 'complemento','sueldo_neto'];
					
					$tempHeaders	= [];
					foreach($subHeaderArray as $k => $subHeader)
					{
						if($k <= 5)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol1);
						}
						elseif($k <= 11)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
						}
						elseif($k <= 18)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
						}
						else
						{
							$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol4);
						}
					}
					$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
					$writer->addRow($rowFromValues);

					$tempCurp		= '';
					$kindRow		= true;
					$flagAlimony	= false;
					foreach($arraySalary as $key => $salary)
					{
						if($tempCurp != $salary['fullname'])
						{
							$tempCurp = $salary['fullname'];
							$kindRow = !$kindRow;
							
						}
						$tmpArr = [];
						$tmpArr[] = WriterEntityFactory::createCell($salary['fullname']);
						$tmpArr[] = WriterEntityFactory::createCell($salary['sd']);
						$tmpArr[] = WriterEntityFactory::createCell($salary['sdi']);
						$tmpArr[] = WriterEntityFactory::createCell($salary['workedDays']);
						$tmpArr[] = WriterEntityFactory::createCell($salary['periodicity']);
						$tmpArr[] = WriterEntityFactory::createCell($salary['range']);
						$tmpArr[] = WriterEntityFactory::createCell($salary['salary']);
						$tmpArr[] = WriterEntityFactory::createCell($salary['loan_perception']);
						$tmpArr[] = WriterEntityFactory::createCell($salary['puntuality']);
						$tmpArr[] = WriterEntityFactory::createCell($salary['assistance']);
						$tmpArr[] = WriterEntityFactory::createCell($salary['subsidy']);
						$tmpArr[] = WriterEntityFactory::createCell($salary['totalPerceptions']);
						$tmpArr[] = WriterEntityFactory::createCell($salary['imss']);
						$tmpArr[] = WriterEntityFactory::createCell($salary['infonavit']);
						$tmpArr[] = WriterEntityFactory::createCell($salary['fonacot']);
						$tmpArr[] = WriterEntityFactory::createCell($salary['loan_retention']);
						$tmpArr[] = WriterEntityFactory::createCell($salary['isrRetentions']);
						$tmpArr[] = WriterEntityFactory::createCell($salary['other_retention_concept']);
						$tmpArr[] = WriterEntityFactory::createCell($salary['other_retention_amount']);
						$tmpArr[] = WriterEntityFactory::createCell($salary['totalRetentions']);
						$tmpArr[] = WriterEntityFactory::createCell($salary['netIncome']);
						$tmpArr[] = WriterEntityFactory::createCell($salary['complement']);
						$tmpArr[] = WriterEntityFactory::createCell($salary['total']);						

						if($kindRow)
						{
							$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
						}
						else
						{
							$rowFromValues = WriterEntityFactory::createRow($tmpArr);
						}
						$writer->addRow($rowFromValues);
					
					}
					return $writer->close();
				}

				if ($arrayBonus != null) 
				{
					$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
					$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
					$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('315864')->setFontColor(Color::WHITE)->setFontBold()->build();
					$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('7C9248')->setFontColor(Color::WHITE)->setFontBold()->build();
					$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('8B3C38')->setFontColor(Color::WHITE)->setFontBold()->build();
					$mhStyleCol4	= (new StyleBuilder())->setBackgroundColor('618BCF')->setFontColor(Color::WHITE)->setFontBold()->build();
					$mhStyleCol5	= (new StyleBuilder())->setBackgroundColor('5C4A77')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol1	= (new StyleBuilder())->setBackgroundColor('9ECBDA')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol2	= (new StyleBuilder())->setBackgroundColor('C8D5A1')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol3	= (new StyleBuilder())->setBackgroundColor('D09996')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol4	= (new StyleBuilder())->setBackgroundColor('C9D8EF')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol5	= (new StyleBuilder())->setBackgroundColor('AEA1C4')->setFontColor(Color::WHITE)->setFontBold()->build();
					$writer			= WriterEntityFactory::createXLSXWriter();
					$writer->setDefaultRowStyle($defaultStyle)->openToBrowser($request->title.'.xlsx');

					$writer->getCurrentSheet()->setName('Aguinaldo');
					$headerArray	= ['INFORMACIÓN','','','','','','PERCEPCIONES','','','RETENCIONES','',''];
					$tempHeaders		= [];
					foreach($headerArray as $k => $header)
					{
						if($k <= 5)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
						}
						elseif($k <= 8)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
						}
						elseif($k <= 10)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
						}
						else
						{
							$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
						}
					}
					$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
					$writer->addRow($rowFromValues);

					$subHeaderArray	= ['empleado','sd','sdi','fecha_de_ingreso','dias_para_aguinaldo','parte_proporcional_para_aguinaldo','aguinaldo_exento','aguinaldo_gravable','total_de_percepciones','isr','total_de_retenciones','sueldo_neto'];
					
					$tempHeaders	= [];
					foreach($subHeaderArray as $k => $subHeader)
					{
						if($k <= 5)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol1);
						}
						elseif($k <= 8)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
						}
						elseif($k <= 10)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
						}
						else
						{
							$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol4);
						}
					}
					$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
					$writer->addRow($rowFromValues);

					$tempCurp		= '';
					$kindRow		= true;
					$flagAlimony	= false;
					foreach($arrayBonus as $key => $bonus)
					{
						if($tempCurp != $bonus['fullname'])
						{
							$tempCurp = $bonus['fullname'];
							$kindRow = !$kindRow;
							
						}
						$tmpArr = [];
						$tmpArr[] = WriterEntityFactory::createCell($bonus['fullname']);
						$tmpArr[] = WriterEntityFactory::createCell($bonus['sd']);
						$tmpArr[] = WriterEntityFactory::createCell($bonus['sdi']);
						$tmpArr[] = WriterEntityFactory::createCell($bonus['fechaIngreso']);
						$tmpArr[] = WriterEntityFactory::createCell($bonus['diasParaAguinaldo']);
						$tmpArr[] = WriterEntityFactory::createCell($bonus['parteProporcionalParaAguinaldo']);
						$tmpArr[] = WriterEntityFactory::createCell($bonus['aguinaldoExento']);
						$tmpArr[] = WriterEntityFactory::createCell($bonus['aguinaldoGravable']);
						$tmpArr[] = WriterEntityFactory::createCell($bonus['totalPercepciones']);
						$tmpArr[] = WriterEntityFactory::createCell($bonus['isr']);
						$tmpArr[] = WriterEntityFactory::createCell($bonus['totalRetenciones']);
						$tmpArr[] = WriterEntityFactory::createCell($bonus['netIncome']);					

						if($kindRow)
						{
							$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
						}
						else
						{
							$rowFromValues = WriterEntityFactory::createRow($tmpArr);
						}
						$writer->addRow($rowFromValues);
					
					}
					return $writer->close();
				}
				if ($arrayLiquidation != null) 
				{
					$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
					$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
					$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('315864')->setFontColor(Color::WHITE)->setFontBold()->build();
					$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('7C9248')->setFontColor(Color::WHITE)->setFontBold()->build();
					$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('8B3C38')->setFontColor(Color::WHITE)->setFontBold()->build();
					$mhStyleCol4	= (new StyleBuilder())->setBackgroundColor('618BCF')->setFontColor(Color::WHITE)->setFontBold()->build();
					$mhStyleCol5	= (new StyleBuilder())->setBackgroundColor('5C4A77')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol1	= (new StyleBuilder())->setBackgroundColor('9ECBDA')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol2	= (new StyleBuilder())->setBackgroundColor('C8D5A1')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol3	= (new StyleBuilder())->setBackgroundColor('D09996')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol4	= (new StyleBuilder())->setBackgroundColor('C9D8EF')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol5	= (new StyleBuilder())->setBackgroundColor('AEA1C4')->setFontColor(Color::WHITE)->setFontBold()->build();
					$writer			= WriterEntityFactory::createXLSXWriter();
					$writer->setDefaultRowStyle($defaultStyle)->openToBrowser($request->title.'.xlsx');

					if ($request->type_payroll == '003') 
					{
						$writer->getCurrentSheet()->setName('Finiquito');
						$headerArray	= ['INFORMACIÓN','','','','','','','','','PERCEPCIONES','','','','','','','','','','RETENCIONES','',''];
						$tempHeaders		= [];
						foreach($headerArray as $k => $header)
						{
							if($k <= 8)
							{
								$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
							}
							elseif($k <= 18)
							{
								$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
							}
							elseif($k <= 20)
							{
								$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
							}
							else
							{
								$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
							}
						}
						$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
						$writer->addRow($rowFromValues);

						$subHeaderArray	= ['empleado','sd','sdi','fecha_de_ingreso','fecha_de_baja','anios_completos','dias_trabajados','dias_para_vacaciones','dias_para_aguinaldo','prima_de_antiguedad','indemnizacion_exenta','indemnizacion_gravada','vacaciones','aguinaldo_exento','aguinaldo_gravable','prima_vacacional_exenta','prima_vacacional_gravada','otras_percepciones','total_de_percepciones','isr','total_de_retenciones','sueldo_neto'];
						
						$tempHeaders	= [];
						foreach($subHeaderArray as $k => $subHeader)
						{
							if($k <= 8)
							{
								$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol1);
							}
							elseif($k <= 18)
							{
								$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
							}
							elseif($k <= 20)
							{
								$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
							}
							else
							{
								$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol4);
							}
						}
						$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
						$writer->addRow($rowFromValues);

						$tempCurp		= '';
						$kindRow		= true;
						$flagAlimony	= false;
						foreach($arrayLiquidation as $key => $liquidation)
						{
							if($tempCurp != $liquidation['fullname'])
							{
								$tempCurp = $liquidation['fullname'];
								$kindRow = !$kindRow;
								
							}
							$tmpArr = [];
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['fullname']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['sd']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['sdi']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['fechaIngreso']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['fechaBaja']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['añosCompletos']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['diasTrabajadosM']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['diasParaVacaciones']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['diasParaAguinaldo']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['primaAntiguedad']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['indemnizacionExcenta']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['indemnizacionGravada']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['vacaciones']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['aguinaldoExento']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['aguinaldoGravable']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['primaVacacionalExenta']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['primaVacacionalGravada']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['otrasPercepciones']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['totalPercepciones']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['totalISR']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['totalRetenciones']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['netIncome']);

							if($kindRow)
							{
								$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
							}
							else
							{
								$rowFromValues = WriterEntityFactory::createRow($tmpArr);
							}
							$writer->addRow($rowFromValues);
						
						}
						return $writer->close();
					}

					if ($request->type_payroll == '004') 
					{
						$writer->getCurrentSheet()->setName('Liquidación');
						$headerArray	= ['INFORMACIÓN','','','','','','','','','PERCEPCIONES','','','','','','','','','','','','RETENCIONES','',''];
						$tempHeaders		= [];
						foreach($headerArray as $k => $header)
						{
							if($k <= 8)
							{
								$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
							}
							elseif($k <= 20)
							{
								$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
							}
							elseif($k <= 22)
							{
								$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
							}
							else
							{
								$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
							}
						}
						$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
						$writer->addRow($rowFromValues);

						$subHeaderArray	= ['empleado','sd','sdi','fecha_de_ingreso','fecha_de_baja','anios_completos','dias_trabajados','dias_para_vacaciones','dias_para_aguinaldo','sueldo_por_liquidacion','20_dias_por_anio_de_servicio','prima_de_antiguedad','indemnizacion_exenta','indemnizacion_gravada','vacaciones','aguinaldo_exento','aguinaldo_gravable','prima_vacacional_exenta','prima_vacacional_gravada','otras_percepciones','total_de_percepciones','isr','total_de_retenciones','sueldo_neto'];
						
						$tempHeaders	= [];
						foreach($subHeaderArray as $k => $subHeader)
						{
							if($k <= 8)
							{
								$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol1);
							}
							elseif($k <= 20)
							{
								$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
							}
							elseif($k <= 22)
							{
								$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
							}
							else
							{
								$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol4);
							}
						}
						$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
						$writer->addRow($rowFromValues);

						$tempCurp		= '';
						$kindRow		= true;
						$flagAlimony	= false;
						foreach($arrayLiquidation as $key => $liquidation)
						{
							if($tempCurp != $liquidation['fullname'])
							{
								$tempCurp = $liquidation['fullname'];
								$kindRow = !$kindRow;
								
							}
							$tmpArr = [];
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['fullname']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['sd']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['sdi']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['fechaIngreso']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['fechaBaja']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['añosCompletos']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['diasTrabajadosM']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['diasParaVacaciones']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['diasParaAguinaldo']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['sueldoPorLiquidacion']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['veinteDiasPorAñoServicio']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['primaAntiguedad']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['indemnizacionExcenta']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['indemnizacionGravada']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['vacaciones']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['aguinaldoExento']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['aguinaldoGravable']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['primaVacacionalExenta']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['primaVacacionalGravada']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['otrasPercepciones']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['totalPercepciones']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['totalISR']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['totalRetenciones']);
							$tmpArr[] = WriterEntityFactory::createCell($liquidation['netIncome']);

							if($kindRow)
							{
								$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
							}
							else
							{
								$rowFromValues = WriterEntityFactory::createRow($tmpArr);
							}
							$writer->addRow($rowFromValues);
						
						}
						return $writer->close();
					}
				}
				if ($arrayVP != null) 
				{
					$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
					$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
					$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('315864')->setFontColor(Color::WHITE)->setFontBold()->build();
					$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('7C9248')->setFontColor(Color::WHITE)->setFontBold()->build();
					$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('8B3C38')->setFontColor(Color::WHITE)->setFontBold()->build();
					$mhStyleCol4	= (new StyleBuilder())->setBackgroundColor('618BCF')->setFontColor(Color::WHITE)->setFontBold()->build();
					$mhStyleCol5	= (new StyleBuilder())->setBackgroundColor('5C4A77')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol1	= (new StyleBuilder())->setBackgroundColor('9ECBDA')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol2	= (new StyleBuilder())->setBackgroundColor('C8D5A1')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol3	= (new StyleBuilder())->setBackgroundColor('D09996')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol4	= (new StyleBuilder())->setBackgroundColor('C9D8EF')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol5	= (new StyleBuilder())->setBackgroundColor('AEA1C4')->setFontColor(Color::WHITE)->setFontBold()->build();
					$writer			= WriterEntityFactory::createXLSXWriter();
					$writer->setDefaultRowStyle($defaultStyle)->openToBrowser($request->title.'.xlsx');

					$writer->getCurrentSheet()->setName('Prima vacacional');
					$headerArray	= ['INFORMACIÓN','','','','','','PERCEPCIONES','','','','RETENCIONES','',''];
					$tempHeaders		= [];
					foreach($headerArray as $k => $header)
					{
						if($k <= 5)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
						}
						elseif($k <= 9)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
						}
						elseif($k <= 11)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
						}
						else
						{
							$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
						}
					}
					$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
					$writer->addRow($rowFromValues);

					$subHeaderArray	= ['empleado','sd','sdi','fecha_de_ingreso','dias_trabajados','dias_para_vacaciones','vacaciones','prima_vacacional_exenta','prima_vacacional_gravada','total_de_percepciones','isr','total_de_retenciones','sueldo_neto'];
					
					$tempHeaders	= [];
					foreach($subHeaderArray as $k => $subHeader)
					{
						if($k <= 5)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol1);
						}
						elseif($k <= 9)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
						}
						elseif($k <= 11)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
						}
						else
						{
							$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol4);
						}
					}
					$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
					$writer->addRow($rowFromValues);

					$tempCurp		= '';
					$kindRow		= true;
					$flagAlimony	= false;
					foreach($arrayVP as $key => $vp)
					{
						if($tempCurp != $vp['fullname'])
						{
							$tempCurp = $vp['fullname'];
							$kindRow = !$kindRow;
							
						}
						$tmpArr = [];
						$tmpArr[] = WriterEntityFactory::createCell($vp['fullname']);
						$tmpArr[] = WriterEntityFactory::createCell($vp['sd']);
						$tmpArr[] = WriterEntityFactory::createCell($vp['sdi']);
						$tmpArr[] = WriterEntityFactory::createCell($vp['dateOfAdmission']);
						$tmpArr[] = WriterEntityFactory::createCell($vp['workedDays']);
						$tmpArr[] = WriterEntityFactory::createCell($vp['holidaysDays']);
						$tmpArr[] = WriterEntityFactory::createCell($vp['holidays']);
						$tmpArr[] = WriterEntityFactory::createCell($vp['exemptHolidayPremium']);
						$tmpArr[] = WriterEntityFactory::createCell($vp['holidayPremiumTaxed']);
						$tmpArr[] = WriterEntityFactory::createCell($vp['totalPerceptions']);
						$tmpArr[] = WriterEntityFactory::createCell($vp['isr']);
						$tmpArr[] = WriterEntityFactory::createCell($vp['totalTaxes']);
						$tmpArr[] = WriterEntityFactory::createCell($vp['netIncome']);

						if($kindRow)
						{
							$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
						}
						else
						{
							$rowFromValues = WriterEntityFactory::createRow($tmpArr);
						}
						$writer->addRow($rowFromValues);
					
					}
					return $writer->close();
				}

				if ($arrayPS != null) 
				{
					$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
					$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
					$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('315864')->setFontColor(Color::WHITE)->setFontBold()->build();
					$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('7C9248')->setFontColor(Color::WHITE)->setFontBold()->build();
					$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('8B3C38')->setFontColor(Color::WHITE)->setFontBold()->build();
					$mhStyleCol4	= (new StyleBuilder())->setBackgroundColor('618BCF')->setFontColor(Color::WHITE)->setFontBold()->build();
					$mhStyleCol5	= (new StyleBuilder())->setBackgroundColor('5C4A77')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol1	= (new StyleBuilder())->setBackgroundColor('9ECBDA')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol2	= (new StyleBuilder())->setBackgroundColor('C8D5A1')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol3	= (new StyleBuilder())->setBackgroundColor('D09996')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol4	= (new StyleBuilder())->setBackgroundColor('C9D8EF')->setFontColor(Color::WHITE)->setFontBold()->build();
					$shStyleCol5	= (new StyleBuilder())->setBackgroundColor('AEA1C4')->setFontColor(Color::WHITE)->setFontBold()->build();
					$writer			= WriterEntityFactory::createXLSXWriter();
					$writer->setDefaultRowStyle($defaultStyle)->openToBrowser($request->title.'.xlsx');

					$writer->getCurrentSheet()->setName('Reparto de Utilidades');
					$headerArray	= ['INFORMACIÓN','','','','','','','','','PERCEPCIONES','','','RETENCIONES','',''];
					$tempHeaders		= [];
					foreach($headerArray as $k => $header)
					{
						if($k <= 9)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
						}
						elseif($k <= 12)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
						}
						elseif($k <= 14)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
						}
						else
						{
							$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
						}
					}
					$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
					$writer->addRow($rowFromValues);

					$subHeaderArray	= ['empleado','fecha_de_ingreso','sd','sdi','dias_trabajados','sueldo_total','ptu_por_dias','ptu_por_sueldos','ptu_total','ptu_exenta','ptu_gravada','total_de_percepciones','isr','total_de_retenciones','sueldo_neto'];
					
					$tempHeaders	= [];
					foreach($subHeaderArray as $k => $subHeader)
					{
						if($k <= 9)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol1);
						}
						elseif($k <= 12)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
						}
						elseif($k <= 14)
						{
							$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
						}
						else
						{
							$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol4);
						}
					}
					$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
					$writer->addRow($rowFromValues);

					$tempCurp		= '';
					$kindRow		= true;
					$flagAlimony	= false;
					foreach($arrayPS as $key => $ps)
					{
						if($tempCurp != $ps['fullname'])
						{
							$tempCurp = $ps['fullname'];
							$kindRow = !$kindRow;
							
						}
						$tmpArr = [];
						$tmpArr[] = WriterEntityFactory::createCell($ps['fullname']);
						$tmpArr[] = WriterEntityFactory::createCell($ps['fechaIngreso']);
						$tmpArr[] = WriterEntityFactory::createCell($ps['sd']);
						$tmpArr[] = WriterEntityFactory::createCell($ps['sdi']);
						$tmpArr[] = WriterEntityFactory::createCell($ps['diasTrabajadosM']);
						$tmpArr[] = WriterEntityFactory::createCell($ps['sueldoTotal']);
						$tmpArr[] = WriterEntityFactory::createCell($ps['ptuPorDias']);
						$tmpArr[] = WriterEntityFactory::createCell($ps['ptuPorSueldos']);
						$tmpArr[] = WriterEntityFactory::createCell($ps['ptuTotal']);
						$tmpArr[] = WriterEntityFactory::createCell($ps['ptuExenta']);
						$tmpArr[] = WriterEntityFactory::createCell($ps['ptuGravada']);
						$tmpArr[] = WriterEntityFactory::createCell($ps['totalPercepciones']);
						$tmpArr[] = WriterEntityFactory::createCell($ps['isr']);
						$tmpArr[] = WriterEntityFactory::createCell($ps['totalRetenciones']);
						$tmpArr[] = WriterEntityFactory::createCell($ps['netIncome']);

						if($kindRow)
						{
							$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
						}
						else
						{
							$rowFromValues = WriterEntityFactory::createRow($tmpArr);
						}
						$writer->addRow($rowFromValues);
					
					}
					return $writer->close();
				}

				if($arrayNomina != null)
				{
					$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
					$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
					$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setFontBold()->build();
					$writer			= WriterEntityFactory::createXLSXWriter();
					$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Formato de nómina.xlsx');
					$writer->getCurrentSheet()->setName('EMP PREEX');
					$headerArray	= ['REGISTRO DE EMPLEADOS PREEXISTENTES','','','','','','','','',''];
					$tempHeaders		= [];
					foreach($headerArray as $k => $header)
					{
						$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
					}
					$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
					$writer->addRow($rowFromValues);

					$subHeaderArray	= ['CURP','EMPRESA','PROYECTO','NOMBRE','APELLIDO_PATERNO','APELLIDO_MATERNO','MONTO_FISCAL','MONTO_TOTAL','COMPLEMENTO','NOTA'];
					
					$tempHeaders	= [];
					foreach($subHeaderArray as $k => $subHeader)
					{
						$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol1);
					}
					$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
					$writer->addRow($rowFromValues);
					$tempCurp	= '';
					$kindRow			= true;
					$flagAlimony 		= false;
					foreach($arrayNomina as $key => $nom)
					{
						if($tempCurp != $nom['curp'])
						{
							$tempCurp = $nom['curp'];
							$kindRow = !$kindRow;
							
						}
						$tmpArr = [];
						$tmpArr[] = WriterEntityFactory::createCell($nom['curp']);
						$tmpArr[] = WriterEntityFactory::createCell($nom['empresa']);
						$tmpArr[] = WriterEntityFactory::createCell($nom['proyecto']);
						$tmpArr[] = WriterEntityFactory::createCell($nom['nombre']);
						$tmpArr[] = WriterEntityFactory::createCell($nom['apellido_paterno']);
						$tmpArr[] = WriterEntityFactory::createCell($nom['apellido_materno']);
						$tmpArr[] = WriterEntityFactory::createCell($nom['monto_fiscal']);
						$tmpArr[] = WriterEntityFactory::createCell($nom['monto_total']);
						$tmpArr[] = WriterEntityFactory::createCell($nom['complemento']);
						$tmpArr[] = WriterEntityFactory::createCell('');

						if($kindRow)
						{
							$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
						}
						else
						{
							$rowFromValues = WriterEntityFactory::createRow($tmpArr);
						}
						$writer->addRow($rowFromValues);
					
					}
					return $writer->close();
				}
				
			}
		}
	}

	public function nominaCalculator(Request $request)
	{
		if (Auth::user()->module->where('id',194)->count() > 0) 
		{
			$data	= App\Module::find(194);
			return view('administracion.nomina.calculadora',
			[
				'id'		=> 271,
				'title'		=> $data['name'],
				'details'	=> $data['details'],
				'child_id'	=> 271,
				'option_id'	=> 194
			]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function formCalculator(Request $r)
	{
		if (Auth::user()->module->where('id',194)->count() > 0) 
		{
			if ($r->ajax()) 
			{
				$employee = App\RealEmployee::find($r->employee);
				switch ($r->type_payroll) 
				{
					case '001':
						return view('administracion.nomina.partial.sueldo')->with('employee',$employee);
						break;

					case '002':
						return view('administracion.nomina.partial.aguinaldo')->with('employee',$employee);
						break;

					case '003':
					case '004':
						return view('administracion.nomina.partial.liquidacion')->with('employee',$employee);
						break;

					case '005':
						return view('administracion.nomina.partial.prima_vacacional')->with('employee',$employee);
						break;

					case '006':
						return view('administracion.nomina.partial.utilidades')->with('employee',$employee);
						break;
					
					default:
						# code...
						break;
				}

			}
		}
	}

	public function calculatorExcel(Request $request)
	{
		$arraySalary = $arrayBonus = $arrayLiquidation = $arrayVP = $arrayPS = [];
		switch ($request->type_payroll) 
		{
			case '001':
				for ($i=0; $i < count($request->fullname); $i++) 
				{ 
					$newFromDate	= $request->from_date	!= "" ? Carbon::createFromFormat('d-m-Y',$request->from_date)->format('Y-m-d') : null;
					$newToDate		= $request->to_date		!= "" ? Carbon::createFromFormat('d-m-Y',$request->to_date)->format('Y-m-d') : null;
					$calculations	= [];
					//calculo para dias de vacaciones
						$calculations['admissionDate']	= $request->admission_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->admission_date[$i])->format('Y-m-d') : null;
						$calculations['nowDate']		= Carbon::now();
						$calculations['diasTrabajados'] = App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['admissionDate'],$calculations['nowDate']);
						$calculations['yearsWork']		= ceil($calculations['diasTrabajados']/365);
						if ($calculations['yearsWork'] > 24) 
						{
							$calculations['vacationDays']	= 20;
						}
						else
						{
							$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
						}

					//-------------------

					$calculations['prima_vac_esp']	= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
					
					if ($request->sdi[$i] > 0) 
					{
						$calculations['sdi'] = $request->sdi[$i]; //
					}
					else
					{
						switch ($request->periodicity) 
						{
							case '02':
								$calculations['divisor_sdi'] = 7;
								break;

							case '04':
								$calculations['divisor_sdi'] = 15;
								break;

							case '05':
								$d = new DateTime(Carbon::now());
								$calculations['divisor_sdi'] = App\Http\Controllers\AdministracionNominaController::days_month($d->format('m'),$d->format('Y'));
								break;
							
							default:
								break;
						}

						$calculations['sdi']	= $request->net_income[$i]/$calculations['divisor_sdi'];
					}
					$calculations['sd']	= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);

					$daysStart = 0;	
					if ($newFromDate < $calculations['admissionDate']) 
					{
						$datetime1	= $newFromDate;
						$datetime2	= $calculations['admissionDate'];
						$interval	= $datetime1->diff($datetime2);
						$daysStart	= $interval->format('%a');
					}
					else
					{
						$daysStart = 0;
					}

					$downDate = $request->downDate[$i] != '' && Carbon::createFromFormat('d-m-Y',$request->downDate[$i])->format('Y-m-d') > $calculations['admissionDate'] ? Carbon::createFromFormat('d-m-Y',$request->downDate[$i])->format('Y-m-d') : null;
					$daysDown = 0;
					if ($downDate !='' && $downDate >= $newFromDate && $downDate <= $newToDate) 
					{
						$date1		= $downDate;
						$date2		= $newToDate;
						$daysDown	= $date1->diff($date2)->days;
					}
					else
					{
						$daysDown = 0;
					}
					$calculations['workedDays']		= (App\CatPeriodicity::find($request->periodicity)->days)-$request->absence[$i]-$daysStart-$daysDown;
					$calculations['periodicity']	= App\CatPeriodicity::find($request->periodicity)->description;
					$calculations['rangeDate']		= $newFromDate.' '.$newToDate;

					switch ($request->periodicity) 
					{
						case '02':
							$d = new \DateTime($newFromDate);
							$d->modify('next thursday');
							$calculations['divisorDayFormImss'] = App\Http\Controllers\AdministracionNominaController::days_count($d->format('m'),$d->format('Y'),4);
							break;

						case '04':
							$calculations['divisorDayFormImss'] = 2;
							break;

						case '05':
							$calculations['divisorDayFormImss'] = 1;
							break;
						
						default:
							break;
					}
					$d = new \DateTime($newFromDate);
					$d->modify('next thursday');
					$calculations['daysMonth'] 		= App\Http\Controllers\AdministracionNominaController::days_month($d->format('m'),$d->format('Y'));

					switch ($request->periodicity) 
					{
						case '02':
							if ($calculations['workedDays']<7) 
							{
								$calculations['daysForImss']	= $calculations['workedDays'];
							}
							else
							{
								$calculations['daysForImss']	= $calculations['daysMonth']/$calculations['divisorDayFormImss'];
							}
							break;

						case '04':
							if ($calculations['workedDays']<15) 
							{
								$calculations['daysForImss']	= $calculations['workedDays'];
							}
							else
							{
								$calculations['daysForImss']	= $calculations['daysMonth']/$calculations['divisorDayFormImss'];
							}
							break;

						case '05':
							if ($calculations['workedDays']<30) 
							{
								$calculations['daysForImss']	= $calculations['workedDays'];
							}
							else
							{
								$calculations['daysForImss']	= $calculations['daysMonth']/$calculations['divisorDayFormImss'];
							}
							break;
						
						default:
							# code...
							break;
					}
					
					
					//PERCEPCIONES
					$calculations['salary']			= $calculations['sd']*$calculations['workedDays'];
					$calculations['loanPerception']	= $request->loan_perception[$i];
					$calculations['puntuality']		= $calculations['salary'] * (($request->bono_puntuality[$i]/100)/2);
					$calculations['assistance']		= $calculations['salary'] * (($request->bono_assistance[$i]/100)/2);

					//calculo para el subsidio

					$calculations['baseTotalDePercepciones']	= $calculations['salary'] + $calculations['puntuality'] + $calculations['assistance'];
					$calculations['baseISR']					= ($calculations['baseTotalDePercepciones']/$calculations['workedDays'])*30.4;
					
					$parameterISR								= App\ParameterISR::where('inferior','<=',$calculations['baseISR'])->where('lapse',30)->get();
					if(count($parameterISR)>0)
					{
						$calculations['limiteInferior']			= $parameterISR->last()->inferior;
						$calculations['excedente']				= $calculations['baseISR']-$calculations['limiteInferior'];
						$calculations['factor']					= $parameterISR->last()->excess/100;
						$calculations['isrMarginal']			= $calculations['excedente'] * $calculations['factor'];
						$calculations['cuotaFija']				= $parameterISR->last()->quota;
						$calculations['isrAntesDelSubsidio']	= (($calculations['isrMarginal'] + $calculations['cuotaFija'])/30.4)*$calculations['workedDays'];
						$parameterSubsidy						= App\ParameterSubsidy::where('inferior','<=',$calculations['baseISR'])->where('lapse',30)->get();

						if ($calculations['baseISR'] <= 7382.34) 
						{
							$calculations['subsidioAlEmpleo'] = ($parameterSubsidy->last()->subsidy/30.4)*$calculations['workedDays'];
						}
						else
						{
							$calculations['subsidioAlEmpleo'] = 0;
						}

						if (($calculations['isrAntesDelSubsidio'] - $calculations['subsidioAlEmpleo']) > 0) 
						{
							$calculations['isrARetener']	= $calculations['isrAntesDelSubsidio'] - $calculations['subsidioAlEmpleo'];
							$calculations['subsidio']		= 0; 	
						}
						else
						{
							$calculations['isrARetener']	= 0;
							$calculations['subsidio']		= round(($calculations['isrAntesDelSubsidio'] - $calculations['subsidioAlEmpleo'])*(-1),2); 	
						}

						$calculations['totalPerceptions']	= round($calculations['salary'],2) + round($calculations['loanPerception'],2) + round($calculations['puntuality'],2) + round($calculations['assistance'],2) + round($calculations['subsidio'],2);

						
						//----------------------------

						//RETENCIONES

						// calculo de IMSS (cuotas obrero-patronal)
						$calculations['SalarioBaseDeCotizacion']	= $calculations['sdi'];
						$calculations['diasDelPeriodoMensual']		= $calculations['daysForImss'];
						$calculations['diasDelPeriodoBimestral']	= $calculations['daysForImss'];
						$calculations['uma']						= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
						$calculations['primaDeRiesgoDeTrabajo']		= App\EmployerRegister::where('employer_register',$request->employer_register[$i])->first()->risk_number; 
						
						if (($calculations['uma']*3) > $calculations['SalarioBaseDeCotizacion'])
						{
							$calculations['imssExcedente'] 			= 0;
						}
						else
						{
							$calculations['imssExcedente']			= ((($calculations['SalarioBaseDeCotizacion']-(3*$calculations['uma']))*$calculations['diasDelPeriodoMensual'])*0.4)/100;
						}
						$calculations['prestacionesEnDinero']		= (($calculations['SalarioBaseDeCotizacion']*$calculations['diasDelPeriodoMensual'])*0.25)/100;
						$calculations['gastosMedicosPensionados']	= (($calculations['SalarioBaseDeCotizacion']*$calculations['diasDelPeriodoMensual'])*0.375)/100;
						$calculations['invalidezVidaPatronal']		= (($calculations['SalarioBaseDeCotizacion']*$calculations['diasDelPeriodoMensual'])*0.625)/100;
						$calculations['cesantiaVejez']				= (($calculations['SalarioBaseDeCotizacion']*$calculations['diasDelPeriodoMensual'])*1.125)/100;

						$calculations['imss'] = $calculations['imssExcedente']+$calculations['prestacionesEnDinero']+$calculations['gastosMedicosPensionados']+$calculations['invalidezVidaPatronal']+$calculations['cesantiaVejez'];

						//calculo infonavit

						$calculations['diasBimestre']		= App\Http\Controllers\AdministracionNominaController::days_bimester($request->from_date);
						$calculations['factorInfonavit']	= App\Parameter::where('parameter_name','INFONAVIT_FACTOR')->first()->parameter_value;

						if ($request->infonavitDiscountType[$i] != '') 
						{
							$calculations['descuentoEmpleado']	= $request->infonavitDiscount[$i];
							$calculations['quinceBimestral']	= App\Http\Controllers\AdministracionNominaController::pay_infonavit($request->from_date,$request->to_date);
							switch ($request->infonavitDiscountType[$i]) 
							{
								case 1:
									$calculations['descuentoInfonavitTemp'] = (($calculations['descuentoEmpleado']*$calculations['factorInfonavit']*2)/$calculations['diasBimestre'])*$calculations['daysForImss']+$calculations['quinceBimestral']; 
									break;

								case 2:
									$calculations['descuentoInfonavitTemp'] = $calculations['descuentoEmpleado']*2/$calculations['diasBimestre']*$calculations['daysForImss']+$calculations['quinceBimestral']; 
									break;

								case 3:
									$calculations['descuentoInfonavitTemp'] = (($calculations['sdi']*($calculations['descuentoEmpleado']/100)*$calculations['daysForImss']))+$calculations['quinceBimestral'];
									break;

								default:
									# code...
									break;
							}
						}
						else
						{
							$calculations['descuentoInfonavitTemp'] = 0 ;
						}

						// -------------------

						$calculations['fonacot']			=(($request->fonacot[$i]/30.4)*$calculations['daysForImss']);
						$calculations['loanRetention']		= $request->loan_retention[$i];
						$calculations['otherRetentionConcept']	= $request->other_retention_concept[$i];
						$calculations['otherRetentionAmount']	= $request->other_retention_amount[$i];

						$calculations['totalRetentionsTempOne']	= round($calculations['imss'],2)+round($calculations['descuentoInfonavitTemp'],2)+round($calculations['fonacot'],2)+round($calculations['loanRetention'],2)+round($calculations['isrARetener'],2)+round($calculations['otherRetentionAmount'],2);

						$calculations['percentage']  	= ($calculations['totalRetentionsTempOne'] * 100) / $calculations['salary'];

						if ($calculations['percentage']>80) 
						{
							$calculations['descuentoInfonavit']		= 0 ;
							$calculations['descuentoInfonavitComplemento'] = $calculations['descuentoInfonavitTemp'];

						}
						else
						{
							$calculations['descuentoInfonavit']		= $calculations['descuentoInfonavitTemp'];
							$calculations['descuentoInfonavitComplemento'] = 0;
						}

						//pensión alimenticia

						if ($request->alimonyDiscountType[$i] != '') 
						{
							$calculations['totalRetentionsTemp']	= round($calculations['imss'],2)+round($calculations['descuentoInfonavit'],2)+round($calculations['fonacot'],2)+round($calculations['loanRetention'],2)+round($calculations['isrARetener'],2)+round($calculations['otherRetentionAmount'],2);
						
							$calculations['netIncomeTemp']			= $calculations['totalPerceptions']-$calculations['totalRetentionsTemp'];

							switch ($request->alimonyDiscountType[$i]) 
							{
								case 1: //monto
									$calculations['amountAlimony']	= $request->alimonyDiscount[$i];
									$calculations['alimony']		= $calculations['amountAlimony'];
									break;

								case 2: // porcentaje
									$calculations['amountAlimony']	= $request->alimonyDiscount[$i];
									$calculations['alimony']		= ($calculations['netIncomeTemp']*$calculations['amountAlimony'])/100;
									break;
								default:
									# code...
									break;
							}

							$calculations['totalRetentions']	= round($calculations['imss'],2)+round($calculations['descuentoInfonavit'],2)+round($calculations['fonacot'],2)+round($calculations['loanRetention'],2)+round($calculations['isrARetener'],2)+round($calculations['alimony'],2)+round($calculations['otherRetentionAmount'],2);
							$calculations['netIncome']			= $calculations['totalPerceptions']-$calculations['totalRetentions'];
						}
						else
						{ 
							$calculations['alimony']			= 0;
							$calculations['totalRetentions']	= round($calculations['imss'],2)+round($calculations['descuentoInfonavit'],2)+round($calculations['fonacot'],2)+round($calculations['loanRetention'],2)+round($calculations['isrARetener'],2)+round($calculations['alimony'],2)+round($calculations['otherRetentionAmount'],2);
							$calculations['netIncome']			= $calculations['totalPerceptions']-$calculations['totalRetentions'];
						}

						
						$calculations['complemento']		= $request->net_income[$i] - $calculations['netIncome']-$calculations['descuentoInfonavitComplemento'] ;

						//return $calculations;

						$arraySalary[$i]['fullname']			= $request->fullname[$i];		
						$arraySalary[$i]['sd']					= $calculations['sd'];
						$arraySalary[$i]['sdi']					= $calculations['sdi'];
						$arraySalary[$i]['workedDays']			= $calculations['workedDays'];
						$arraySalary[$i]['periodicity']			= App\CatPeriodicity::find($request->periodicity)->description;
						$arraySalary[$i]['range']				= $newFromDate.' '.$newToDate;
						$arraySalary[$i]['salary']				= $calculations['salary'];
						$arraySalary[$i]['loan_perception']		= $calculations['loanPerception'];
						$arraySalary[$i]['puntuality']			= $calculations['puntuality'];
						$arraySalary[$i]['assistance']			= $calculations['assistance'];
						$arraySalary[$i]['subsidy']				= $calculations['subsidio'];
						$arraySalary[$i]['totalPerceptions']	= $calculations['totalPerceptions'];
						$arraySalary[$i]['imss']				= $calculations['imss'];
						$arraySalary[$i]['infonavit']			= $calculations['descuentoInfonavit'];
						$arraySalary[$i]['fonacot']				= $calculations['fonacot'];
						$arraySalary[$i]['loan_retention']		= $calculations['loanRetention'];
						$arraySalary[$i]['isrRetentions']		= $calculations['isrARetener'];
						$arraySalary[$i]['alimony']  			= $calculations['alimony'];
						$arraySalary[$i]['totalRetentions']		= $calculations['totalRetentions'];
						$arraySalary[$i]['netIncome']			= $calculations['netIncome'];
						$arraySalary[$i]['complement'] 			= $calculations['complemento'];
						$arraySalary[$i]['total'] 				= $request->net_income[$i];
						$calculations	= [];
					}
				}
				break;

			case '002':
				for ($i=0; $i < count($request->fullname); $i++) 
				{ 
					$calculations = [];
					//calculo para dias de vacaciones
					$calculations['fechaIngreso']		= $request->admission_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->admission_date[$i])->format('Y-m-d') : null;
					$calculations['fechaActual']		= Carbon::now();
					$calculations['diasTrabajados']		= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
					$calculations['yearsWork']			= ceil($calculations['diasTrabajados']/365);
					if ($calculations['yearsWork'] > 24) 
					{
						$calculations['vacationDays']	= 20;
					}
					else
					{
						$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
					}

					//-------------------

					$calculations['prima_vac_esp']	= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
					$calculations['sdi']			= $request->sdi[$i];
					$calculations['sd']				= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);
					$calculations['uma']							= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
					$calculations['exento']							= $calculations['uma']*30; 
					$calculations['diasParaAguinaldo']				= $request->day_bonus[$i];
					$calculations['parteProporcionalParaAguinaldo']	= round((15*$calculations['diasParaAguinaldo'])/365,6);


					// PERCEPCIONES AGUINALDO---------------------------------------------------------------------

					if (($calculations['parteProporcionalParaAguinaldo'] * $calculations['sd']) < $calculations['exento']) 
					{
						$calculations['aguinaldoExento'] = $calculations['parteProporcionalParaAguinaldo'] * $calculations['sd'];
					}
					else
					{
						$calculations['aguinaldoExento'] = $calculations['exento'];
					}

					if (($calculations['parteProporcionalParaAguinaldo'] * $calculations['sd']) > $calculations['exento']) 
					{
						$calculations['aguinaldoGravable'] = ($calculations['parteProporcionalParaAguinaldo'] * $calculations['sd'])-$calculations['aguinaldoExento'];
					}
					else
					{
						$calculations['aguinaldoGravable'] = 0;
					}

					$calculations['totalPercepciones'] = round($calculations['aguinaldoExento'],2) + round($calculations['aguinaldoGravable'],2);

					// --------------------------------------------------------------------------------------------

					// RETENCIONES- ISR ---------------------------------------------------------------------

					// ISR 1ER FRACCION

					$calculations['baseISR_fraccion1']			= round((($calculations['aguinaldoGravable']/365)*30.4)+($calculations['sd']*30),6);
					$parameterISRF1								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion1'])->where('lapse',30)->get();
					if(count($parameterISRF1) > 0)
					{

						$calculations['limiteInferior_fraccion1']	= $parameterISRF1->last()->inferior;
						$calculations['excedente_fraccion1']		= round($calculations['baseISR_fraccion1']-$calculations['limiteInferior_fraccion1'],6);
						$calculations['factor_fraccion1']			= round($parameterISRF1->last()->excess/100,6);
						$calculations['isrMarginal_fraccion1']		= round($calculations['excedente_fraccion1'] * $calculations['factor_fraccion1'],6);
						$calculations['cuotaFija_fraccion1']		= round($parameterISRF1->last()->quota,6);
						$calculations['isr_fraccion1']				= round($calculations['isrMarginal_fraccion1']+$calculations['cuotaFija_fraccion1'],6);
	
						// ISR 2DA FRACCION
	
						$calculations['baseISR_fraccion2']			= round($calculations['sd']*30,6);
						$parameterISRF2								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion2'])->where('lapse',30)->get();
	
						$calculations['limiteInferior_fraccion2']	= $parameterISRF2->last()->inferior;
						$calculations['excedente_fraccion2']		= round($calculations['baseISR_fraccion2']-$calculations['limiteInferior_fraccion2'],6);
						$calculations['factor_fraccion2']			= round($parameterISRF2->last()->excess/100,6);
						$calculations['isrMarginal_fraccion2']		= round($calculations['excedente_fraccion2'] * $calculations['factor_fraccion2'],6);
						$calculations['cuotaFija_fraccion2']		= round($parameterISRF2->last()->quota,6);
						$calculations['isr_fraccion2']				= round($calculations['isrMarginal_fraccion2']+$calculations['cuotaFija_fraccion2'],6);
	
						$calculations['resta']		= round($calculations['isr_fraccion1']-$calculations['isr_fraccion2'],6);
						$calculations['factor1']	= round((($calculations['aguinaldoGravable']/365) * 30.4),6);
						if($calculations['factor1'] == 0)
						{
							$calculations['factor2']	= 0;
						}
						else
						{
							$calculations['factor2']	= round($calculations['resta']/$calculations['factor1'],6);
						}
						$calculations['isr']		= round($calculations['factor2']*$calculations['aguinaldoGravable'],6);
						//pensión alimenticia
	
						if ($request->alimonyDiscountType[$i] != '') 
						{
							$calculations['totalRetencionesTemp']	= round($calculations['isr'],2);
						
							$calculations['netIncomeTemp']			= $calculations['totalPercepciones']-$calculations['totalRetencionesTemp'];
	
							switch ($request->alimonyDiscountType[$i]) 
							{
								case 1: //monto
									$calculations['amountAlimony']	= $request->alimonyDiscount[$i];
									$calculations['alimony']		= $calculations['amountAlimony'];
									break;
	
								case 2: // porcentaje
									$calculations['amountAlimony']	= $request->alimonyDiscount[$i];
									$calculations['alimony']		= ($calculations['netIncomeTemp']*$calculations['amountAlimony'])/100;
									break;
								default:
									# code...
									break;
							}
	
							$calculations['totalRetenciones']	= round($calculations['isr'],2)+round($calculations['alimony'],2);
							$calculations['netIncome']			= $calculations['totalPercepciones']-$calculations['totalRetenciones'];
						}
						else
						{ 
							$calculations['alimony']			= 0;
							$calculations['totalRetenciones']	= round($calculations['isr'],2)+round($calculations['alimony'],2);
							$calculations['netIncome']			= $calculations['totalPercepciones']-$calculations['totalRetenciones'];
						}
	
						// --------------------------------------------------------------------------------------------
	
						$arrayBonus[$i]['fullname']							= $request->fullname[$i];
						$arrayBonus[$i]['sd']								= $calculations['sd'];
						$arrayBonus[$i]['sdi']								= $calculations['sdi'];
						$arrayBonus[$i]['fechaIngreso']						= $calculations['fechaIngreso'];
						$arrayBonus[$i]['diasParaAguinaldo']				= $calculations['diasParaAguinaldo'];
						$arrayBonus[$i]['parteProporcionalParaAguinaldo']	= $calculations['parteProporcionalParaAguinaldo'];
						$arrayBonus[$i]['aguinaldoExento']					= $calculations['aguinaldoExento'];
						$arrayBonus[$i]['aguinaldoGravable']				= $calculations['aguinaldoGravable'];
						$arrayBonus[$i]['totalPercepciones']				= $calculations['totalPercepciones'];
						$arrayBonus[$i]['isr']								= $calculations['isr'];
						$arrayBonus[$i]['alimony']							= $calculations['alimony'];
						$arrayBonus[$i]['totalRetenciones']					= $calculations['totalRetenciones'];
						$arrayBonus[$i]['netIncome']						= $calculations['netIncome'];
						$calculations = [];
					}
				}
				
				break;

			case '003':
			case '004':
				for ($i=0; $i < count($request->fullname); $i++) 
				{ 
					// ----- calculo para dias de vacaciones ---------------------------
					$calculations					= [];

					$calculations['fechaIngreso']	= $request->admission_date[$i]	!= "" ? Carbon::createFromFormat('d-m-Y',$request->admission_date[$i])->format('Y-m-d') : null;
					$calculations['fechaBaja']		= $request->down_date[$i]		!= "" ? Carbon::createFromFormat('d-m-Y',$request->down_date[$i])->format('Y-m-d') 		: null;
					$calculations['fechaActual']	= Carbon::now();
					$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
					$calculations['añosTrabajados']	= ceil($calculations['diasTrabajados']/365);

					$calculations['diasTrabajadosParaAñosCompletos'] = App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaBaja']);

					$calculations['añosCompletos']	= floor($calculations['diasTrabajadosParaAñosCompletos']/365);
					if ($calculations['añosTrabajados'] > 24) 
					{
						$calculations['diasDeVacaciones']	= 20;
					}
					else
					{
						$calculations['diasDeVacaciones']	= App\ParameterVacation::where('fromYear','<=',$calculations['añosTrabajados'])->where('toYear','>=',$calculations['añosTrabajados'])->first()->days;
					}

					//------------------------------------------------------------------
					
					$calculations['prima_vac_esp']		= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
					$calculations['sdi']				= $request->sdi[$i];
					$calculations['sd']					= round($calculations['sdi']/((($calculations['diasDeVacaciones']*$calculations['prima_vac_esp'])+15+365)/365),2);
					
					$calculations['diasTrabajadosM']	= $request->worked_days[$i];
					
					$calculations['diasParaVacaciones']	= ($calculations['diasDeVacaciones']*$calculations['diasTrabajadosM'])/365;
					//dias trabajados para aguinaldo va del 1 de enero a la fecha de baja
					$date1 = new \DateTime(date("Y").'-01-01');
					$date2 = $calculations['fechaIngreso'];
					if ($date2 > $date1) 
					{
						$fechaParaDiasAguinaldo = $calculations['fechaIngreso'];
					}
					else
					{
						$fechaParaDiasAguinaldo = date("Y").'-01-01';
					}
					$calculations['diasTrabajadosParaAguinaldo'] = App\Http\Controllers\AdministracionNominaController::daysPassed($fechaParaDiasAguinaldo,$calculations['fechaBaja'])+1;

					$calculations['diasParaAguinaldo'] 	= ($calculations['diasTrabajadosParaAguinaldo']*15)/365;

					if ($request->type_payroll == '004') 
					{
						$calculations['sueldoPorLiquidacion']		= round($calculations['sd']*90,6);
						$calculations['veinteDiasPorAñoServicio']	= round(20*$calculations['añosCompletos']*$calculations['sd'],6);
						
						// VARIABLES -------------------------------------------------------
						$calculations['uma']							= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
						$calculations['salarioMinimo']					= App\Parameter::where('parameter_name','SALARY_VDF')->first()->parameter_value; 
						$calculations['valorPrimaAntiguedad']			= $calculations['salarioMinimo']*2;
						$calculations['exento']							= $calculations['uma']*90; 
						$calculations['valorAguinaldoExento']			= $calculations['uma']*30; 
						$calculations['valorPrimaVacacaionalExenta']	= $calculations['uma']*15; 
						$calculations['valorIndemnizacionExenta']		= $calculations['uma']*90;
						//  PRIMA DE ANTIGUEDAD ------------------------------------------------------------------

						if ($calculations['sd']>=$calculations['valorPrimaAntiguedad']) 
						{
							$calculations['primaAntiguedad'] = round($calculations['añosCompletos']*12*$calculations['valorPrimaAntiguedad'],6);
						}
						else
						{
							$calculations['primaAntiguedad'] = round($calculations['añosCompletos']*12*$calculations['sd'],6);
						}

						//  INDEMNIZACION ------------------------------------------------------------------
						$calculations['indemnizacion'] =  round($calculations['sueldoPorLiquidacion']+$calculations['veinteDiasPorAñoServicio']+$calculations['primaAntiguedad'],6);

						if ($calculations['indemnizacion'] < $calculations['valorIndemnizacionExenta']) 
						{
							$calculations['indemnizacionExcenta']	= $calculations['indemnizacion'];
						}
						else
						{
							$calculations['indemnizacionExcenta']	= $calculations['valorIndemnizacionExenta'];
						}


						if ($calculations['indemnizacion'] > $calculations['valorIndemnizacionExenta']) 
						{
							$calculations['indemnizacionGravada']	= $calculations['indemnizacion']-$calculations['indemnizacionExcenta'];
						}
						else
						{
							$calculations['indemnizacionGravada']	= 0;
						}

						$calculations['vacaciones']				= $calculations['diasParaVacaciones']*$calculations['sd'];


						// PERCEPCIONES AGUINALDO---------------------------------------------------------------------

						if (($calculations['diasParaAguinaldo'] * $calculations['sd']) < $calculations['valorAguinaldoExento']) 
						{
							$calculations['aguinaldoExento'] = $calculations['diasParaAguinaldo'] * $calculations['sd'];
						}
						else
						{
							$calculations['aguinaldoExento'] = $calculations['valorAguinaldoExento'];
						}

						if (($calculations['diasParaAguinaldo'] * $calculations['sd']) > $calculations['valorAguinaldoExento']) 
						{
							$calculations['aguinaldoGravable'] = ($calculations['diasParaAguinaldo'] * $calculations['sd'])-$calculations['aguinaldoExento'];
						}
						else
						{
							$calculations['aguinaldoGravable'] = 0;
						}


						//-------- PERCEPCIONES ---------------------------------------------------------------


						if (($calculations['vacaciones']*$calculations['prima_vac_esp'])<$calculations['valorPrimaVacacaionalExenta'])
						{
							$calculations['primaVacacionalExenta'] = round($calculations['vacaciones']*$calculations['prima_vac_esp'],6);
						}
						else
						{
							$calculations['primaVacacionalExenta'] = $calculations['valorPrimaVacacaionalExenta'];
						}

						if (($calculations['vacaciones']*$calculations['prima_vac_esp'])>$calculations['valorPrimaVacacaionalExenta'])
						{
							$calculations['primaVacacionalGravada'] = round(($calculations['vacaciones']*$calculations['prima_vac_esp'])-$calculations['primaVacacionalExenta'],6);
						}
						else
						{
							$calculations['primaVacacionalGravada'] = 0;
						}

						$calculations['otrasPercepciones'] = $request->other_perception[$i];

						$calculations['totalPercepciones'] = round($calculations['sueldoPorLiquidacion'],2)+round($calculations['veinteDiasPorAñoServicio'],2)+round($calculations['primaAntiguedad'],2)+round($calculations['vacaciones'],2)+round($calculations['aguinaldoExento'],2)+round($calculations['aguinaldoGravable'],2)+round($calculations['primaVacacionalExenta'],2)+round($calculations['primaVacacionalGravada'],2)+round($calculations['otrasPercepciones'],2);
					}
					else
					{
						// VARIABLES -------------------------------------------------------
						$calculations['uma']							= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
						$calculations['salarioMinimo']					= App\Parameter::where('parameter_name','SALARY_VDF')->first()->parameter_value; 
						$calculations['valorPrimaAntiguedad']			= $calculations['salarioMinimo']*2;
						$calculations['exento']							= $calculations['uma']*90; 
						$calculations['valorAguinaldoExento']			= $calculations['uma']*30; 
						$calculations['valorPrimaVacacaionalExenta']	= $calculations['uma']*15; 
						$calculations['valorIndemnizacionExenta']		= $calculations['uma']*90;
						//  PRIMA DE ANTIGUEDAD ------------------------------------------------------------------

						if ($calculations['sd']>=$calculations['valorPrimaAntiguedad']) 
						{
							$calculations['primaAntiguedad'] = round($calculations['añosCompletos']*12*$calculations['valorPrimaAntiguedad'],6);
						}
						else
						{
							$calculations['primaAntiguedad'] = round($calculations['añosCompletos']*12*$calculations['sd'],6);
						}

						//  INDEMNIZACION  ------------------------------------------------------------------

						if ($calculations['primaAntiguedad'] < $calculations['valorIndemnizacionExenta']) 
						{
							$calculations['indemnizacionExcenta']	= $calculations['primaAntiguedad'];
						}
						else
						{
							$calculations['indemnizacionExcenta']	= $calculations['valorIndemnizacionExenta'];
						}


						if ($calculations['primaAntiguedad'] > $calculations['valorIndemnizacionExenta']) 
						{
							$calculations['indemnizacionGravada']	= $calculations['primaAntiguedad']-$calculations['indemnizacionExcenta'];
						}
						else
						{
							$calculations['indemnizacionGravada']	= 0;
						}

						$calculations['vacaciones']				= $calculations['diasParaVacaciones']*$calculations['sd'];


						// PERCEPCIONES AGUINALDO---------------------------------------------------------------------

						if (($calculations['diasParaAguinaldo'] * $calculations['sd']) < $calculations['valorAguinaldoExento']) 
						{
							$calculations['aguinaldoExento'] = $calculations['diasParaAguinaldo'] * $calculations['sd'];
						}
						else
						{
							$calculations['aguinaldoExento'] = $calculations['valorAguinaldoExento'];
						}

						if (($calculations['diasParaAguinaldo'] * $calculations['sd']) > $calculations['valorAguinaldoExento']) 
						{
							$calculations['aguinaldoGravable'] = ($calculations['diasParaAguinaldo'] * $calculations['sd'])-$calculations['aguinaldoExento'];
						}
						else
						{
							$calculations['aguinaldoGravable'] = 0;
						}


						//-------- PERCEPCIONES PRIMA VACACIONAL ---------------------------------------------------------------


						if (($calculations['vacaciones']*$calculations['prima_vac_esp'])<$calculations['valorPrimaVacacaionalExenta'])
						{
							$calculations['primaVacacionalExenta'] = round($calculations['vacaciones']*$calculations['prima_vac_esp'],6);
						}
						else
						{
							$calculations['primaVacacionalExenta'] = $calculations['valorPrimaVacacaionalExenta'];
						}

						if (($calculations['vacaciones']*$calculations['prima_vac_esp'])>$calculations['valorPrimaVacacaionalExenta'])
						{
							$calculations['primaVacacionalGravada'] = round(($calculations['vacaciones']*$calculations['prima_vac_esp'])-$calculations['primaVacacionalExenta'],6);
						}
						else
						{
							$calculations['primaVacacionalGravada'] = 0;
						}

						$calculations['otrasPercepciones'] = $request->other_perception[$i];

						$calculations['totalPercepciones'] = round($calculations['primaAntiguedad'],2)+round($calculations['vacaciones'],2)+round($calculations['aguinaldoExento'],2)+round($calculations['aguinaldoGravable'],2)+round($calculations['primaVacacionalExenta'],2)+round($calculations['primaVacacionalGravada'],2)+round($calculations['otrasPercepciones'],2);

						// ------------------------------------------------------------------------------------
					}

					//-------- RETENCIONES ----------------------------------------------------------------

					// ISR 1ER FRACCION

					$calculations['baseISR_fraccion1']			= round(((($calculations['vacaciones']+$calculations['aguinaldoGravable']+$calculations['primaVacacionalGravada'])/365)*30.4)+($calculations['sd']*30),6);
					$parameterISRF1								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion1'])->where('lapse',30)->get();
					if(count($parameterISRF1) > 0)
					{

						$calculations['limiteInferior_fraccion1']	= $parameterISRF1->last()->inferior;
						$calculations['excedente_fraccion1']		= round($calculations['baseISR_fraccion1']-$calculations['limiteInferior_fraccion1'],6);
						$calculations['factor_fraccion1']			= round($parameterISRF1->last()->excess/100,6);
						$calculations['isrMarginal_fraccion1']		= round($calculations['excedente_fraccion1'] * $calculations['factor_fraccion1'],6);
						$calculations['cuotaFija_fraccion1']		= round($parameterISRF1->last()->quota,6);
						$calculations['isr_fraccion1']				= round($calculations['isrMarginal_fraccion1']+$calculations['cuotaFija_fraccion1'],6);
	
						// ISR 2DA FRACCION
	
						$calculations['baseISR_fraccion2']			= round($calculations['sd']*30,6);
						$parameterISRF2								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion2'])->where('lapse',30)->get();
	
						$calculations['limiteInferior_fraccion2']	= $parameterISRF2->last()->inferior;
						$calculations['excedente_fraccion2']		= round($calculations['baseISR_fraccion2']-$calculations['limiteInferior_fraccion2'],6);
						$calculations['factor_fraccion2']			= round($parameterISRF2->last()->excess/100,6);
						$calculations['isrMarginal_fraccion2']		= round($calculations['excedente_fraccion2'] * $calculations['factor_fraccion2'],6);
						$calculations['cuotaFija_fraccion2']		= round($parameterISRF2->last()->quota,6);
						$calculations['isr_fraccion2']				= round($calculations['isrMarginal_fraccion2']+$calculations['cuotaFija_fraccion2'],6);
	
						$calculations['resta']		= round($calculations['isr_fraccion1']-$calculations['isr_fraccion2'],6);
						if ($calculations['resta'] == 0) 
						{
							$calculations['factor1']	= 0;
							$calculations['factor2']	= 0;
							$calculations['isr']		= 0;
						}
						else
						{
							$calculations['factor1']	= round(((($calculations['vacaciones']+$calculations['aguinaldoGravable']+$calculations['primaVacacionalGravada'])/365)*30.4),6);
							$calculations['factor2']	= round($calculations['resta']/$calculations['factor1'],6);
							$calculations['isr']		= round($calculations['factor2']*($calculations['vacaciones']+$calculations['aguinaldoGravable']+$calculations['primaVacacionalGravada']),6);
						}
	
						// ISR FINIQUITO (INDEMNIZACION)
	
						$calculations['baseTotalDePercepciones']	= round($calculations['sd']*30,6);
						$calculations['baseISR_finiquito']			= $calculations['baseTotalDePercepciones'];
						
						$parameterISRFiniquito						= App\ParameterISR::where('inferior','<=',$calculations['baseISR_finiquito'])->where('lapse',30)->get();
						
						$calculations['limiteInferior_finiquito']	= $parameterISRFiniquito->last()->inferior;
						$calculations['excedente_finiquito']		= round($calculations['baseISR_finiquito']-$calculations['limiteInferior_finiquito'],6);
						$calculations['factor_finiquito']			= round($parameterISRFiniquito->last()->excess/100,6);
						$calculations['isrMarginal_finiquito']		= round($calculations['excedente_finiquito'] * $calculations['factor_finiquito'],6);
						$calculations['cuotaFija_finiquito']		= round($parameterISRFiniquito->last()->quota,6);
						$calculations['isr_salario']				= round($calculations['isrMarginal_finiquito']+$calculations['cuotaFija_finiquito'],6);
						
						$calculations['isr_finiquito']				= round(($calculations['isr_salario']/$calculations['baseTotalDePercepciones'])*$calculations['indemnizacionGravada'],6);
						
						$calculations['totalISR']					= $calculations['isr_finiquito'] + $calculations['isr']; 
	
						if ($request->alimonyDiscountType[$i] != '') 
						{
							$calculations['totalRetencionesTemp']	= round($calculations['totalISR'],2);
						
							$calculations['netIncomeTemp']			= $calculations['totalPercepciones']-$calculations['totalRetencionesTemp'];
	
							switch ($request->alimonyDiscountType[$i]) 
							{
								case 1: //monto
									$calculations['amountAlimony']	= $request->alimonyDiscount[$i];
									$calculations['alimony']		= $calculations['amountAlimony'];
									break;
	
								case 2: // porcentaje
									$calculations['amountAlimony']	= $request->alimonyDiscount[$i];
									$calculations['alimony']		= ($calculations['netIncomeTemp']*$calculations['amountAlimony'])/100;
									break;
								default:
									# code...
									break;
							}
	
							$calculations['totalRetenciones']	= round($calculations['totalISR'],2)+round($calculations['alimony'],2);
							$calculations['netIncome']			= $calculations['totalPercepciones']-$calculations['totalRetenciones'];
						}
						else
						{ 
							$calculations['alimony']			= 0;
							$calculations['totalRetenciones']	= round($calculations['totalISR'],2)+round($calculations['alimony'],2);
							$calculations['netIncome']			= $calculations['totalPercepciones']-$calculations['totalRetenciones'];
						}
	
						// --------------------------------------------------------------------------------------------
	
						$arrayLiquidation[$i]['fullname']			= $request->fullname[$i];
						$arrayLiquidation[$i]['sd']					= $calculations['sd'];
						$arrayLiquidation[$i]['sdi']				= $calculations['sdi'];
						$arrayLiquidation[$i]['fechaIngreso']		= $calculations['fechaIngreso'];
						$arrayLiquidation[$i]['fechaBaja']			= $calculations['fechaBaja'];
						$arrayLiquidation[$i]['añosCompletos']		= $calculations['añosCompletos'];
						$arrayLiquidation[$i]['diasTrabajadosM']	= $calculations['diasTrabajadosM'];
						$arrayLiquidation[$i]['diasParaVacaciones']	= $calculations['diasParaVacaciones'];
						$arrayLiquidation[$i]['diasParaAguinaldo']	= $calculations['diasParaAguinaldo'];
						if ($request->type_payroll == '004') 
						{
							$arrayLiquidation[$i]['sueldoPorLiquidacion']			= $calculations['sueldoPorLiquidacion'];
							$arrayLiquidation[$i]['veinteDiasPorAñoServicio']	= $calculations['veinteDiasPorAñoServicio'];
						}
						$arrayLiquidation[$i]['primaAntiguedad']		= $calculations['primaAntiguedad'];
						$arrayLiquidation[$i]['indemnizacionExcenta']	= $calculations['indemnizacionExcenta'];
						$arrayLiquidation[$i]['indemnizacionGravada']	= $calculations['indemnizacionGravada'];
						$arrayLiquidation[$i]['vacaciones']				= $calculations['vacaciones'];
						$arrayLiquidation[$i]['aguinaldoExento']		= $calculations['aguinaldoExento'];
						$arrayLiquidation[$i]['aguinaldoGravable']		= $calculations['aguinaldoGravable'];
						$arrayLiquidation[$i]['primaVacacionalExenta']	= $calculations['primaVacacionalExenta'];
						$arrayLiquidation[$i]['primaVacacionalGravada']	= $calculations['primaVacacionalGravada'];
						$arrayLiquidation[$i]['otrasPercepciones']		= $calculations['otrasPercepciones'];
						$arrayLiquidation[$i]['totalPercepciones']		= $calculations['totalPercepciones'];
						$arrayLiquidation[$i]['totalISR']				= $calculations['totalISR'];
						$arrayLiquidation[$i]['alimony']				= $calculations['alimony'];
						$arrayLiquidation[$i]['totalRetenciones']		= $calculations['totalRetenciones'];
						$arrayLiquidation[$i]['netIncome']				= $calculations['netIncome'];
	
						$calculations = [];
					}
				}

				break;

			case '005':
				for ($i=0; $i < count($request->fullname); $i++) 
				{ 					

					// ----- calculo para dias de vacaciones ---------------------------
					$calculations					= [];
					$calculations['fechaIngreso']	= $request->admission_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->admission_date[$i])->format('Y-m-d') : null;
					$calculations['fechaActual']	= Carbon::now();
					$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
					$calculations['yearsWork']		= ceil($calculations['diasTrabajados']/365);
					if ($calculations['yearsWork'] > 24) 
					{
						$calculations['vacationDays']	= 20;
					}
					else
					{
						$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
					}

					//------------------------------------------------------------------
					
					$calculations['prima_vac_esp']		= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
					$calculations['sdi']				= $request->sdi[$i];
					$calculations['sd']					= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);
					
					$calculations['diasTrabajadosM']	= $request->worked_days[$i];
					
					$calculations['diasParaVacaciones']	= ($calculations['vacationDays']*$calculations['diasTrabajadosM'])/365;
					
					$calculations['uma']				= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
					$calculations['exento']				= $calculations['uma']*15; 

					//-------- PERCEPCIONES ---------------------------------------------------------------

					$calculations['vacaciones'] = $calculations['sd']*$calculations['diasParaVacaciones'];

					if (($calculations['sd']*$calculations['diasParaVacaciones']*$calculations['prima_vac_esp'])<$calculations['exento'])
					{
						$calculations['primaVacacionalExenta'] = round($calculations['sd']*$calculations['diasParaVacaciones']*$calculations['prima_vac_esp'],6);
					}
					else
					{
						$calculations['primaVacacionalExenta'] = $calculations['exento'];
					}

					if (($calculations['sd']*$calculations['diasParaVacaciones']*$calculations['prima_vac_esp'])>$calculations['exento'])
					{
						$calculations['primaVacacionalGravada'] = round(($calculations['sd']*$calculations['diasParaVacaciones']*$calculations['prima_vac_esp'])-$calculations['primaVacacionalExenta'],6);
					}
					else
					{
						$calculations['primaVacacionalGravada'] = 0;
					}

					$calculations['totalPercepciones'] = round($calculations['primaVacacionalExenta'],2)+round($calculations['primaVacacionalGravada'],2);

					// ------------------------------------------------------------------------------------

					//-------- RETENCIONES ----------------------------------------------------------------

					// ISR 1ER FRACCION

					$calculations['baseISR_fraccion1']			= round((($calculations['primaVacacionalGravada']/365)*30.4)+($calculations['sd']*30),6);
					$parameterISRF1								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion1'])->where('lapse',30)->get();
					if(count($parameterISRF1) > 0)
					{

						$calculations['limiteInferior_fraccion1']	= $parameterISRF1->last()->inferior;
						$calculations['excedente_fraccion1']		= round($calculations['baseISR_fraccion1']-$calculations['limiteInferior_fraccion1'],6);
						$calculations['factor_fraccion1']			= round($parameterISRF1->last()->excess/100,6);
						$calculations['isrMarginal_fraccion1']		= round($calculations['excedente_fraccion1'] * $calculations['factor_fraccion1'],6);
						$calculations['cuotaFija_fraccion1']		= round($parameterISRF1->last()->quota,6);
						$calculations['isr_fraccion1']				= round($calculations['isrMarginal_fraccion1']+$calculations['cuotaFija_fraccion1'],6);
	
						// ISR 2DA FRACCION
	
						$calculations['baseISR_fraccion2']			= round($calculations['sd']*30,6);
						$parameterISRF2								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion2'])->where('lapse',30)->get();
	
						$calculations['limiteInferior_fraccion2']	= $parameterISRF2->last()->inferior;
						$calculations['excedente_fraccion2']		= round($calculations['baseISR_fraccion2']-$calculations['limiteInferior_fraccion2'],6);
						$calculations['factor_fraccion2']			= round($parameterISRF2->last()->excess/100,6);
						$calculations['isrMarginal_fraccion2']		= round($calculations['excedente_fraccion2'] * $calculations['factor_fraccion2'],6);
						$calculations['cuotaFija_fraccion2']		= round($parameterISRF2->last()->quota,6);
						$calculations['isr_fraccion2']				= round($calculations['isrMarginal_fraccion2']+$calculations['cuotaFija_fraccion2'],6);
	
						$calculations['resta']		= round($calculations['isr_fraccion1']-$calculations['isr_fraccion2'],6);
						if ($calculations['resta'] == 0) 
						{
							$calculations['factor1']	= 0;
							$calculations['factor2']	= 0;
							$calculations['isr']		= 0;
						}
						else
						{
							$calculations['factor1']	= round((($calculations['primaVacacionalGravada']/365) * 30.4),6);
							$calculations['factor2']	= round($calculations['resta']/$calculations['factor1'],6);
							$calculations['isr']		= round($calculations['factor2']*$calculations['primaVacacionalGravada'],6);
						}
	
						//pensión alimenticia
	
						if ($request->alimonyDiscountType[$i] != '') 
						{
							$calculations['totalRetencionesTemp']	= round($calculations['isr'],2);
						
							$calculations['netIncomeTemp']			= $calculations['totalPercepciones']-$calculations['totalRetencionesTemp'];
	
							switch ($request->alimonyDiscountType[$i]) 
							{
								case 1: //monto
									$calculations['amountAlimony']	= $request->alimonyDiscount[$i];
									$calculations['alimony']		= $calculations['amountAlimony'];
									break;
	
								case 2: // porcentaje
									$calculations['amountAlimony']	= $request->alimonyDiscount[$i];
									$calculations['alimony']		= ($calculations['netIncomeTemp']*$calculations['amountAlimony'])/100;
									break;
								default:
									# code...
									break;
							}
	
							$calculations['totalRetenciones']	= round($calculations['isr'],2)+round($calculations['alimony'],2);
							$calculations['netIncome']			= $calculations['totalPercepciones']-$calculations['totalRetenciones'];
						}
						else
						{ 
							$calculations['alimony']			= 0;
							$calculations['totalRetenciones']	= round($calculations['isr'],2)+round($calculations['alimony'],2);
							$calculations['netIncome']			= $calculations['totalPercepciones']-$calculations['totalRetenciones'];
						}
	
						$arrayVP[$i]['fullname']				= $request->fullname[$i];
						$arrayVP[$i]['sd']						= $calculations['sd'];
						$arrayVP[$i]['sdi']						= $calculations['sdi'];
						$arrayVP[$i]['dateOfAdmission']			= $calculations['fechaIngreso'];
						$arrayVP[$i]['workedDays']				= $calculations['diasTrabajadosM'];
						$arrayVP[$i]['holidaysDays']			= $calculations['diasParaVacaciones'];
						$arrayVP[$i]['holidays']				= $calculations['vacaciones'];
						$arrayVP[$i]['exemptHolidayPremium']	= $calculations['primaVacacionalExenta'];
						$arrayVP[$i]['holidayPremiumTaxed']		= $calculations['primaVacacionalGravada'];
						$arrayVP[$i]['totalPerceptions']		= $calculations['totalPercepciones'];
						$arrayVP[$i]['isr']						= $calculations['isr'];
						$arrayVP[$i]['alimony']					= $calculations['alimony'];
						$arrayVP[$i]['totalTaxes']				= $calculations['totalRetenciones'];
						$arrayVP[$i]['netIncome']				= $calculations['netIncome'];
	
						$calculations = [];
					}
				}
			
				break;

			case '006':
				$sumaDiasTrabajados	= 0;
				$sumaSueldoTotal	= 0;
				//------- calculo para sumatoria de dias trabajados y sueldo total ------------------------
				for ($i=0; $i < count($request->fullname); $i++) 
				{					
					$sumaDiasTrabajados		 		+= $request->worked_days[$i];
					$calculations					= [];
					$calculations['fechaIngreso']	= $request->admission_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->admission_date[$i])->format('Y-m-d') : null;
					$calculations['fechaActual']	= Carbon::now();
					$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
					$calculations['yearsWork']		= ceil($calculations['diasTrabajados']/365);
					if ($calculations['yearsWork'] > 24) 
					{
						$calculations['vacationDays']	= 20;
					}
					else
					{
						$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
					}


					$calculations['prima_vac_esp']	= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
					$calculations['sdi']			= $request->sdi[$i];
					$calculations['sd']				= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);

					$sumaSueldoTotal += round($request->worked_days[$i] * $calculations['sd'],6);
					$calculations = [];
				}

				// -------------------------------------------------------------------------------------------------------
				for ($i=0; $i < count($request->fullname); $i++) 
				{ 
					// ----- calculo para dias de vacaciones ---------------------------
					$calculations					= [];
					$calculations['fechaIngreso']	= $request->admission_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->admission_date[$i])->format('Y-m-d') : null;
					$calculations['fechaActual']	= Carbon::now();
					$calculations['diasTrabajados']	= App\Http\Controllers\AdministracionNominaController::daysPassed($calculations['fechaIngreso'],$calculations['fechaActual']);
					$calculations['yearsWork']		= ceil($calculations['diasTrabajados']/365);
					if ($calculations['yearsWork'] > 24) 
					{
						$calculations['vacationDays']	= 20;
					}
					else
					{
						$calculations['vacationDays']	= App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->count()>0 ? App\ParameterVacation::where('fromYear','<=',$calculations['yearsWork'])->where('toYear','>=',$calculations['yearsWork'])->first()->days : 0;
					}

					//------------------------------------------------------------------
					
					$calculations['prima_vac_esp']		= App\Parameter::where('parameter_name','PRIMA_VAC_ESP')->first()->parameter_value * 0.01;
					$calculations['sdi']				= $request->sdi[$i];
					$calculations['sd']					= round($calculations['sdi']/((($calculations['vacationDays']*$calculations['prima_vac_esp'])+15+365)/365),2);
					
					$calculations['diasTrabajadosM']	= $request->worked_days[$i];
					$calculations['sueldoTotal']		= round($calculations['diasTrabajadosM'] * $calculations['sd'],6);
					
					$calculations['sumaDiasTrabajados']	= $sumaDiasTrabajados;
					$calculations['sumaSueldoTotal']	= $sumaSueldoTotal;

					$calculations['uma']							= App\Parameter::where('parameter_name','UMA')->first()->parameter_value;
					$calculations['exento']							= $calculations['uma']*15; 

					$calculations['ptuPorPagar']		= round($request->ptu_to_pay,6);
					$calculations['factorPorDias']		= round(($calculations['ptuPorPagar']/2)/$calculations['sumaDiasTrabajados'],6);
					$calculations['factorPorSueldo']	= round(($calculations['ptuPorPagar']/2)/$calculations['sumaSueldoTotal'],6);

					$calculations['ptuPorDias']		= round($calculations['diasTrabajadosM'] * $calculations['factorPorDias'],6);
					$calculations['ptuPorSueldos']	= round($calculations['sueldoTotal']*$calculations['factorPorSueldo'],6);
					$calculations['ptuTotal']		= round($calculations['ptuPorDias']+$calculations['ptuPorSueldos'],6);

					//-------- PERCEPCIOONES -------------------------------------------------------------

					$calculations['ptuExenta']			= $calculations['exento'];
					$calculations['ptuGravada']			= round($calculations['ptuTotal']-$calculations['ptuExenta'],6);
					$calculations['totalPercepciones']	= round($calculations['ptuExenta'],2)+round($calculations['ptuGravada'],2);


					// ------------------------------------------------------------------------------------

					//-------- RETENCIONES ----------------------------------------------------------------

					// ISR 1ER FRACCION

					$calculations['baseISR_fraccion1']			= round((($calculations['ptuGravada']/365)*30.4)+($calculations['sd']*30),6);
					$parameterISRF1								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion1'])->where('lapse',30)->get();
					if(count($parameterISRF1) > 0)
					{

						$calculations['limiteInferior_fraccion1']	= $parameterISRF1->last()->inferior;
						$calculations['excedente_fraccion1']		= round($calculations['baseISR_fraccion1']-$calculations['limiteInferior_fraccion1'],6);
						$calculations['factor_fraccion1']			= round($parameterISRF1->last()->excess/100,6);
						$calculations['isrMarginal_fraccion1']		= round($calculations['excedente_fraccion1'] * $calculations['factor_fraccion1'],6);
						$calculations['cuotaFija_fraccion1']		= round($parameterISRF1->last()->quota,6);
						$calculations['isr_fraccion1']				= round($calculations['isrMarginal_fraccion1']+$calculations['cuotaFija_fraccion1'],6);
	
						// ISR 2DA FRACCION
	
						$calculations['baseISR_fraccion2']			= round($calculations['sd']*30,6);
						$parameterISRF2								= App\ParameterISR::where('inferior','<=',$calculations['baseISR_fraccion2'])->where('lapse',30)->get();
	
						$calculations['limiteInferior_fraccion2']	= $parameterISRF2->last()->inferior;
						$calculations['excedente_fraccion2']		= round($calculations['baseISR_fraccion2']-$calculations['limiteInferior_fraccion2'],6);
						$calculations['factor_fraccion2']			= round($parameterISRF2->last()->excess/100,6);
						$calculations['isrMarginal_fraccion2']		= round($calculations['excedente_fraccion2'] * $calculations['factor_fraccion2'],6);
						$calculations['cuotaFija_fraccion2']		= round($parameterISRF2->last()->quota,6);
						$calculations['isr_fraccion2']				= round($calculations['isrMarginal_fraccion2']+$calculations['cuotaFija_fraccion2'],6);
	
						$calculations['resta']		= round($calculations['isr_fraccion1']-$calculations['isr_fraccion2'],6);
						$calculations['factor1']	= round((($calculations['ptuGravada']/365) * 30.4),6);
						if($calculations['factor1'] == 0)
						{
							$calculations['factor2']	= 0;
						}
						else
						{
							$calculations['factor2']	= round($calculations['resta']/$calculations['factor1'],6);
						}
	
						$calculations['isr']		= round($calculations['factor2']*$calculations['ptuGravada'],6);
	
						//pensión alimenticia
	
						if ($request->alimonyDiscountType[$i] != '') 
						{
							$calculations['totalRetencionesTemp']	= round($calculations['isr'],2);
						
							$calculations['netIncomeTemp']			= $calculations['totalPercepciones']-$calculations['totalRetencionesTemp'];
	
							switch ($request->alimonyDiscountType[$i]) 
							{
								case 1: //monto
									$calculations['amountAlimony']	= $request->alimonyDiscount[$i];
									$calculations['alimony']		= $calculations['amountAlimony'];
									break;
	
								case 2: // porcentaje
									$calculations['amountAlimony']	= $request->alimonyDiscount[$i];
									$calculations['alimony']		= ($calculations['netIncomeTemp']*$calculations['amountAlimony'])/100;
									break;
								default:
									# code...
									break;
							}
	
							$calculations['totalRetenciones']	= round($calculations['isr'],2)+round($calculations['alimony'],2);
							$calculations['netIncome']			= $calculations['totalPercepciones']-$calculations['totalRetenciones'];
						}
						else
						{ 
							$calculations['alimony']			= 0;
							$calculations['totalRetenciones']	= round($calculations['isr'],2)+round($calculations['alimony'],2);
							$calculations['netIncome']			= $calculations['totalPercepciones']-$calculations['totalRetenciones'];
						}
	
						// --------------------------------------------------------------------------------------------
	
						$arrayPS[$i]['fullname'] 			= $request->fullname[$i];
						$arrayPS[$i]['fechaIngreso'] 		= $calculations['fechaIngreso'];
						$arrayPS[$i]['sd']					= $calculations['sd'];
						$arrayPS[$i]['sdi']					= $calculations['sdi'];
						$arrayPS[$i]['diasTrabajadosM']		= $calculations['diasTrabajadosM'];
						$arrayPS[$i]['sueldoTotal']			= $calculations['sueldoTotal'];
						$arrayPS[$i]['ptuPorDias']			= $calculations['ptuPorDias'];
						$arrayPS[$i]['ptuPorSueldos']		= $calculations['ptuPorSueldos'];
						$arrayPS[$i]['ptuTotal']			= $calculations['ptuTotal'];
						$arrayPS[$i]['ptuExenta']			= $calculations['ptuExenta'];
						$arrayPS[$i]['ptuGravada']			= $calculations['ptuGravada'];
						$arrayPS[$i]['totalPercepciones']	= $calculations['totalPercepciones'];
						$arrayPS[$i]['isr']					= $calculations['isr'];
						$arrayPS[$i]['alimony']				= $calculations['alimony'];
						$arrayPS[$i]['totalRetenciones']	= $calculations['totalRetenciones'];
						$arrayPS[$i]['netIncome']			= $calculations['netIncome'];
	
	
						$calculations = [];
					}
				}
				break;

			
			default:
				# code...
				break;
		}

		if(isset($parameterISRF1) && (count($parameterISRF1) == 0 || $parameterISRF1 == null))
		{
			return redirect()->back()->with('alert',"swal('', 'Error en el cálculo, por favor revise los datos: SDI, PTU a Pagar.', 'error');");
		}
		
		if ($arraySalary != null) 
		{
			$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('315864')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('7C9248')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('8B3C38')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol4	= (new StyleBuilder())->setBackgroundColor('618BCF')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol5	= (new StyleBuilder())->setBackgroundColor('5C4A77')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol1	= (new StyleBuilder())->setBackgroundColor('9ECBDA')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol2	= (new StyleBuilder())->setBackgroundColor('C8D5A1')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol3	= (new StyleBuilder())->setBackgroundColor('D09996')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol4	= (new StyleBuilder())->setBackgroundColor('C9D8EF')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol5	= (new StyleBuilder())->setBackgroundColor('AEA1C4')->setFontColor(Color::WHITE)->setFontBold()->build();
			$writer			= WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser($request->title.'.xlsx');

			$writer->getCurrentSheet()->setName('Salario');
			$headerArray	= ['INFORMACIÓN','','','','','','PERCEPCIONES','','','','','','RETENCIONES','','','','','',''];
			$tempHeaders		= [];
			foreach($headerArray as $k => $header)
			{
				if($k <= 5)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				elseif($k <= 11)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
				}
				elseif($k <= 17)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
				}
				else
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaderArray	= ['empleado','sd','sdi','dias_trabajados','periodicidad','rango_de_fechas','sueldo','prestamo_percepcion','puntualidad','asistencia','subsidio','total_de_percepciones','imss','infonavit','fonacot','prestamo_retencion','retencion_de_isr','pension_alimenticia','total_de_retenciones','sueldo_neto_fiscal','complemento','sueldo_neto'];
			
			$tempHeaders	= [];
			foreach($subHeaderArray as $k => $subHeader)
			{
				if($k <= 5)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol1);
				}
				elseif($k <= 11)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				elseif($k <= 17)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				else
				{
					$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol4);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$tempCurp		= '';
			$kindRow		= true;
			$flagAlimony	= false;
			foreach($arraySalary as $key => $salary)
			{
				if($tempCurp != $salary['fullname'])
				{
					$tempCurp = $salary['fullname'];
					$kindRow = !$kindRow;
				}

				$tmpArr = [];
				$tmpArr[] = WriterEntityFactory::createCell($salary['fullname']);
				$tmpArr[] = WriterEntityFactory::createCell($salary['sd']);
				$tmpArr[] = WriterEntityFactory::createCell($salary['sdi']);
				$tmpArr[] = WriterEntityFactory::createCell($salary['workedDays']);
				$tmpArr[] = WriterEntityFactory::createCell($salary['periodicity']);
				$tmpArr[] = WriterEntityFactory::createCell($salary['range']);
				$tmpArr[] = WriterEntityFactory::createCell($salary['salary']);
				$tmpArr[] = WriterEntityFactory::createCell($salary['loan_perception']);
				$tmpArr[] = WriterEntityFactory::createCell($salary['puntuality']);
				$tmpArr[] = WriterEntityFactory::createCell($salary['assistance']);
				$tmpArr[] = WriterEntityFactory::createCell($salary['subsidy']);
				$tmpArr[] = WriterEntityFactory::createCell($salary['totalPerceptions']);
				$tmpArr[] = WriterEntityFactory::createCell($salary['imss']);
				$tmpArr[] = WriterEntityFactory::createCell($salary['infonavit']);
				$tmpArr[] = WriterEntityFactory::createCell($salary['fonacot']);
				$tmpArr[] = WriterEntityFactory::createCell($salary['loan_retention']);
				$tmpArr[] = WriterEntityFactory::createCell($salary['isrRetentions']);
				$tmpArr[] = WriterEntityFactory::createCell($salary['alimony']);
				$tmpArr[] = WriterEntityFactory::createCell($salary['totalRetentions']);
				$tmpArr[] = WriterEntityFactory::createCell($salary['netIncome']);
				$tmpArr[] = WriterEntityFactory::createCell($salary['complement']);
				$tmpArr[] = WriterEntityFactory::createCell($salary['total']);					

				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr);
				}
				$writer->addRow($rowFromValues);
			
			}
			return $writer->close();
		}

		if ($arrayBonus != null) 
		{
			$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('315864')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('7C9248')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('8B3C38')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol4	= (new StyleBuilder())->setBackgroundColor('618BCF')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol5	= (new StyleBuilder())->setBackgroundColor('5C4A77')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol1	= (new StyleBuilder())->setBackgroundColor('9ECBDA')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol2	= (new StyleBuilder())->setBackgroundColor('C8D5A1')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol3	= (new StyleBuilder())->setBackgroundColor('D09996')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol4	= (new StyleBuilder())->setBackgroundColor('C9D8EF')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol5	= (new StyleBuilder())->setBackgroundColor('AEA1C4')->setFontColor(Color::WHITE)->setFontBold()->build();
			$writer			= WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser($request->title.'.xlsx');

			$writer->getCurrentSheet()->setName('Aguinaldo');
			$headerArray	= ['INFORMACIÓN','','','','','','PERCEPCIONES','','','RETENCIONES','','',''];
			$tempHeaders		= [];
			foreach($headerArray as $k => $header)
			{
				if($k <= 5)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				elseif($k <= 8)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
				}
				elseif($k <= 11)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
				}
				else
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaderArray	= ['empleado','sd','sdi','fecha_de_ingreso','dias_para_aguinaldo','parte_proporcional_para_aguinaldo','aguinaldo_exento','aguinaldo_gravable','total_de_percepciones','isr','pension_alimenticia','total_de_retenciones','sueldo_neto'];
			
			$tempHeaders	= [];
			foreach($subHeaderArray as $k => $subHeader)
			{
				if($k <= 5)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol1);
				}
				elseif($k <= 8)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				elseif($k <= 11)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				else
				{
					$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol4);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$tempCurp		= '';
			$kindRow		= true;
			$flagAlimony	= false;
			foreach($arrayBonus as $key => $bonus)
			{
				if($tempCurp != $bonus['fullname'])
				{
					$tempCurp = $bonus['fullname'];
					$kindRow = !$kindRow;
					
				}
				$tmpArr = [];
				$tmpArr[] = WriterEntityFactory::createCell($bonus['fullname']);
				$tmpArr[] = WriterEntityFactory::createCell($bonus['fullname']);
				$tmpArr[] = WriterEntityFactory::createCell($bonus['sd']);
				$tmpArr[] = WriterEntityFactory::createCell($bonus['sdi']);
				$tmpArr[] = WriterEntityFactory::createCell($bonus['fechaIngreso']);
				$tmpArr[] = WriterEntityFactory::createCell($bonus['diasParaAguinaldo']);
				$tmpArr[] = WriterEntityFactory::createCell($bonus['parteProporcionalParaAguinaldo']);
				$tmpArr[] = WriterEntityFactory::createCell($bonus['aguinaldoExento']);
				$tmpArr[] = WriterEntityFactory::createCell($bonus['aguinaldoGravable']);
				$tmpArr[] = WriterEntityFactory::createCell($bonus['totalPercepciones']);
				$tmpArr[] = WriterEntityFactory::createCell($bonus['isr']);
				$tmpArr[] = WriterEntityFactory::createCell($bonus['alimony']);
				$tmpArr[] = WriterEntityFactory::createCell($bonus['totalRetenciones']);
				$tmpArr[] = WriterEntityFactory::createCell($bonus['netIncome']);
				
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr);
				}
				$writer->addRow($rowFromValues);
			
			}
			return $writer->close();
		}

		if ($arrayLiquidation != null) 
		{
			$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('315864')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('7C9248')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('8B3C38')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol4	= (new StyleBuilder())->setBackgroundColor('618BCF')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol5	= (new StyleBuilder())->setBackgroundColor('5C4A77')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol1	= (new StyleBuilder())->setBackgroundColor('9ECBDA')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol2	= (new StyleBuilder())->setBackgroundColor('C8D5A1')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol3	= (new StyleBuilder())->setBackgroundColor('D09996')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol4	= (new StyleBuilder())->setBackgroundColor('C9D8EF')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol5	= (new StyleBuilder())->setBackgroundColor('AEA1C4')->setFontColor(Color::WHITE)->setFontBold()->build();
			$writer			= WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser($request->title.'.xlsx');

			if ($request->type_payroll == '003') 
			{
				$writer->getCurrentSheet()->setName('Finiquito');
				$headerArray	= ['INFORMACIÓN','','','','','','','','','PERCEPCIONES','','','','','','','','','','RETENCIONES','','',''];
				$tempHeaders		= [];
				foreach($headerArray as $k => $header)
				{
					if($k <= 8)
					{
						$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
					}
					elseif($k <= 18)
					{
						$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
					}
					elseif($k <= 21)
					{
						$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
					}
					else
					{
						$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaderArray	= ['empleado','sd','sdi','fecha_de_ingreso','fecha_de_baja','anios_completos','dias_trabajados','dias_para_vacaciones','dias_para_aguinaldo','prima_de_antiguedad','indemnizacion_exenta','indemnizacion_gravada','vacaciones','aguinaldo_exento','aguinaldo_gravable','prima_vacacional_exenta','prima_vacacional_gravada','otras_percepciones','total_de_percepciones','isr','pension_alimenticia','total_de_retenciones','sueldo_neto'];
				
				$tempHeaders	= [];
				foreach($subHeaderArray as $k => $subHeader)
				{
					if($k <= 8)
					{
						$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol1);
					}
					elseif($k <= 18)
					{
						$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
					}
					elseif($k <= 21)
					{
						$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
					}
					else
					{
						$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol4);
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$tempCurp		= '';
				$kindRow		= true;
				$flagAlimony	= false;
				foreach($arrayLiquidation as $key => $liquidation)
				{
					if($tempCurp != $liquidation['fullname'])
					{
						$tempCurp	= $liquidation['fullname'];
						$kindRow	= !$kindRow;
						
					}
					$tmpArr		= [];
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['fullname']);
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['sd']);
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['sdi']);
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['fechaIngreso']);
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['fechaBaja']);
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['añosCompletos']);
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['diasTrabajadosM']);
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['diasParaVacaciones']);
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['diasParaAguinaldo']);
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['primaAntiguedad']);
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['indemnizacionExcenta']);
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['indemnizacionGravada']);
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['vacaciones']);
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['aguinaldoExento']);
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['aguinaldoGravable']);
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['primaVacacionalExenta']);
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['primaVacacionalGravada']);
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['otrasPercepciones']);
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['totalPercepciones']);
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['totalISR']);
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['alimony']);
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['totalRetenciones']);
					$tmpArr[]	= WriterEntityFactory::createCell($liquidation['netIncome']);

					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
				
				}
				return $writer->close();
			}

			if ($request->type_payroll == '004') 
			{
				$writer->getCurrentSheet()->setName('Liquidación');
				$headerArray	= ['INFORMACIÓN','','','','','','','','','PERCEPCIONES','','','','','','','','','','','','RETENCIONES','','',''];
				$tempHeaders		= [];
				foreach($headerArray as $k => $header)
				{
					if($k <= 8)
					{
						$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
					}
					elseif($k <= 20)
					{
						$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
					}
					elseif($k <= 23)
					{
						$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
					}
					else
					{
						$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaderArray	= ['empleado','sd','sdi','fecha_de_ingreso','fecha_de_baja','anios_completos','dias_trabajados','dias_para_vacaciones','dias_para_aguinaldo','sueldo_por_liquidacion','20_dias_por_anio_de_servicio','prima_de_antiguedad','indemnizacion_exenta','indemnizacion_gravada','vacaciones','aguinaldo_exento','aguinaldo_gravable','prima_vacacional_exenta','prima_vacacional_gravada','otras_percepciones','total_de_percepciones','isr','pension_alimenticia','total_de_retenciones','sueldo_neto'];
				
				$tempHeaders	= [];
				foreach($subHeaderArray as $k => $subHeader)
				{
					if($k <= 8)
					{
						$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol1);
					}
					elseif($k <= 20)
					{
						$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
					}
					elseif($k <= 23)
					{
						$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
					}
					else
					{
						$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol4);
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$tempCurp		= '';
				$kindRow		= true;
				$flagAlimony	= false;
				foreach($arrayLiquidation as $key => $liquidation)
				{
					if($tempCurp != $liquidation['fullname'])
					{
						$tempCurp = $liquidation['fullname'];
						$kindRow = !$kindRow;
						
					}
					$tmpArr = [];
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['fullname']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['sd']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['sdi']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['fechaIngreso']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['fechaBaja']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['añosCompletos']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['diasTrabajadosM']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['diasParaVacaciones']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['diasParaAguinaldo']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['sueldoPorLiquidacion']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['veinteDiasPorAñoServicio']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['primaAntiguedad']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['indemnizacionExcenta']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['indemnizacionGravada']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['vacaciones']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['aguinaldoExento']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['aguinaldoGravable']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['primaVacacionalExenta']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['primaVacacionalGravada']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['otrasPercepciones']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['totalPercepciones']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['totalISR']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['alimony']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['totalRetenciones']);
					$tmpArr[] = WriterEntityFactory::createCell($liquidation['netIncome']);

					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
				
				}
				return $writer->close();
			}
		}

		if ($arrayVP != null) 
		{
			$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('315864')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('7C9248')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('8B3C38')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol4	= (new StyleBuilder())->setBackgroundColor('618BCF')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol5	= (new StyleBuilder())->setBackgroundColor('5C4A77')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol1	= (new StyleBuilder())->setBackgroundColor('9ECBDA')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol2	= (new StyleBuilder())->setBackgroundColor('C8D5A1')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol3	= (new StyleBuilder())->setBackgroundColor('D09996')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol4	= (new StyleBuilder())->setBackgroundColor('C9D8EF')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol5	= (new StyleBuilder())->setBackgroundColor('AEA1C4')->setFontColor(Color::WHITE)->setFontBold()->build();
			$writer			= WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser($request->title.'.xlsx');

			$writer->getCurrentSheet()->setName('Prima vacacional');
			$headerArray	= ['INFORMACIÓN','','','','','','PERCEPCIONES','','','','RETENCIONES','','',''];
			$tempHeaders		= [];
			foreach($headerArray as $k => $header)
			{
				if($k <= 5)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				elseif($k <= 9)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
				}
				elseif($k <= 12)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
				}
				else
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaderArray	= ['empleado','sd','sdi','fecha_de_ingreso','dias_trabajados','dias_para_vacaciones','vacaciones','prima_vacacional_exenta','prima_vacacional_gravada','total_de_percepciones','isr','pension_alimenticia','total_de_retenciones','sueldo_neto'];
			
			$tempHeaders	= [];
			foreach($subHeaderArray as $k => $subHeader)
			{
				if($k <= 5)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol1);
				}
				elseif($k <= 9)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				elseif($k <= 12)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				else
				{
					$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol4);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$tempCurp		= '';
			$kindRow		= true;
			$flagAlimony	= false;
			foreach($arrayVP as $key => $vp)
			{
				if($tempCurp != $vp['fullname'])
				{
					$tempCurp = $vp['fullname'];
					$kindRow = !$kindRow;
					
				}
				$tmpArr = [];
				$tmpArr[] = WriterEntityFactory::createCell($vp['fullname']);
				$tmpArr[] = WriterEntityFactory::createCell($vp['sd']);
				$tmpArr[] = WriterEntityFactory::createCell($vp['sdi']);
				$tmpArr[] = WriterEntityFactory::createCell($vp['dateOfAdmission']);
				$tmpArr[] = WriterEntityFactory::createCell($vp['workedDays']);
				$tmpArr[] = WriterEntityFactory::createCell($vp['holidaysDays']);
				$tmpArr[] = WriterEntityFactory::createCell($vp['holidays']);
				$tmpArr[] = WriterEntityFactory::createCell($vp['exemptHolidayPremium']);
				$tmpArr[] = WriterEntityFactory::createCell($vp['holidayPremiumTaxed']);
				$tmpArr[] = WriterEntityFactory::createCell($vp['totalPerceptions']);
				$tmpArr[] = WriterEntityFactory::createCell($vp['isr']);
				$tmpArr[] = WriterEntityFactory::createCell($vp['alimony']);
				$tmpArr[] = WriterEntityFactory::createCell($vp['totalTaxes']);
				$tmpArr[] = WriterEntityFactory::createCell($vp['netIncome']);

				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr);
				}
				$writer->addRow($rowFromValues);
			
			}
			return $writer->close();
		}

		if ($arrayPS != null) 
		{
			$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('315864')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('7C9248')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('8B3C38')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol4	= (new StyleBuilder())->setBackgroundColor('618BCF')->setFontColor(Color::WHITE)->setFontBold()->build();
			$mhStyleCol5	= (new StyleBuilder())->setBackgroundColor('5C4A77')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol1	= (new StyleBuilder())->setBackgroundColor('9ECBDA')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol2	= (new StyleBuilder())->setBackgroundColor('C8D5A1')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol3	= (new StyleBuilder())->setBackgroundColor('D09996')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol4	= (new StyleBuilder())->setBackgroundColor('C9D8EF')->setFontColor(Color::WHITE)->setFontBold()->build();
			$shStyleCol5	= (new StyleBuilder())->setBackgroundColor('AEA1C4')->setFontColor(Color::WHITE)->setFontBold()->build();
			$writer			= WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser($request->title.'.xlsx');

			$writer->getCurrentSheet()->setName('Reparto de Utilidades');
			$headerArray	= ['INFORMACIÓN','','','','','','','','','PERCEPCIONES','','','RETENCIONES','','',''];
			$tempHeaders		= [];
			foreach($headerArray as $k => $header)
			{
				if($k <= 9)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				elseif($k <= 12)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
				}
				elseif($k <= 15)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
				}
				else
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaderArray	= ['empleado','fecha_de_ingreso','sd','sdi','dias_trabajados','sueldo_total','ptu_por_dias','ptu_por_sueldos','ptu_total','ptu_exenta','ptu_gravada','total_de_percepciones','isr','pension_alimenticia','total_de_retenciones','sueldo_neto'];
			
			$tempHeaders	= [];
			foreach($subHeaderArray as $k => $subHeader)
			{
				if($k <= 9)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol1);
				}
				elseif($k <= 12)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				elseif($k <= 15)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				else
				{
					$tempHeaders[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol4);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$tempCurp		= '';
			$kindRow		= true;
			$flagAlimony	= false;
			foreach($arrayPS as $key => $ps)
			{
				if($tempCurp != $ps['fullname'])
				{
					$tempCurp = $ps['fullname'];
					$kindRow = !$kindRow;
					
				}
				$tmpArr = [];
				$tmpArr[] = WriterEntityFactory::createCell($ps['fullname']);
				$tmpArr[] = WriterEntityFactory::createCell($ps['fechaIngreso']);
				$tmpArr[] = WriterEntityFactory::createCell($ps['sd']);
				$tmpArr[] = WriterEntityFactory::createCell($ps['sdi']);
				$tmpArr[] = WriterEntityFactory::createCell($ps['diasTrabajadosM']);
				$tmpArr[] = WriterEntityFactory::createCell($ps['sueldoTotal']);
				$tmpArr[] = WriterEntityFactory::createCell($ps['ptuPorDias']);
				$tmpArr[] = WriterEntityFactory::createCell($ps['ptuPorSueldos']);
				$tmpArr[] = WriterEntityFactory::createCell($ps['ptuTotal']);
				$tmpArr[] = WriterEntityFactory::createCell($ps['ptuExenta']);
				$tmpArr[] = WriterEntityFactory::createCell($ps['ptuGravada']);
				$tmpArr[] = WriterEntityFactory::createCell($ps['totalPercepciones']);
				$tmpArr[] = WriterEntityFactory::createCell($ps['isr']);
				$tmpArr[] = WriterEntityFactory::createCell($ps['alimony']);
				$tmpArr[] = WriterEntityFactory::createCell($ps['totalRetenciones']);
				$tmpArr[] = WriterEntityFactory::createCell($ps['netIncome']);

				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr);
				}
				$writer->addRow($rowFromValues);
			
			}
			return $writer->close();
		}
	}

	public function validationEmployerRegister(Request $request)
	{
		$employer_register 	= App\EmployerRegister::where('employer_register',$request->validation_employer_register)->get();
		if($request->validation_employer_register != '')
		{
			if (count($employer_register)>0)
			{
				$response = array('valid' => true);	
			}
			else
			{
				$response = array(
					'valid' 	=> false,
					'message'	=> 'El registro patronal no existe'
				);
			}
		}
		else
		{
			$response = array(
				'valid' 	=> false,
				'message'	=> 'El campo es obligatorio'
			);
		}
		return Response($response);
	}

	public function uploader(Request $request)
	{
		\Tinify\setKey("DDPii23RhemZFX8YXES5OVhEP7UmdXMt");
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
					\Storage::disk('public')->delete('/docs/nomina/'.$request->realPath[$i]);
				}
				
			}
			if($request->file('path'))
			{
				$extention				= strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_nominaDoc.';
				$name					= $nameWithoutExtention.$extention;
				$destinity				= '/docs/nomina/'.$name;
				if($extention=='png' || $extention=='jpg' || $extention=='jpeg')
				{
					try
					{
						$sourceData	= file_get_contents($request->path);
						$resultData	= \Tinify\fromBuffer($sourceData)->toBuffer();
						\Storage::disk('public')->put($destinity,$resultData);
						$response['error']		= 'DONE';
						$response['path']		= $name;
						$response['message']	= '';
						$response['extention']	= strtolower($extention);
					}
					catch(\Tinify\AccountException $e)
					{
						$response['message']	= $e->getMessage();
					}
					catch(\Tinify\ClientException $e)
					{
						$response['message']	= 'Por favor, verifique su archivo.';
					}
					catch(\Tinify\ServerException $e)
					{
						$response['message']	= 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos. Si ve este mensaje por un periodo de tiempo más larga, por favor contacte a soporte con el código: SAPIT2.';
					}
					catch(\Tinify\ConnectionException $e)
					{
						$response['message']	= 'Ocurrió un problema de conexión, por favor verifique su red e intente nuevamente.';
					}
					catch(Exception $e)
					{
						
					}
				}
				else
				{
					\Storage::disk('public')->put($destinity,\File::get($request->path));
					$response['error']		= 'DONE';
					$response['path']		= $name;
					$response['message']	= '';
					$response['extention']	= strtolower($extention);
				}
			}
			return Response($response);
		}
	}

	public function uploadDocuments($id,Request $request)
	{
		if (Auth::user()->module->where('id',166)->count()>0)
		{
			$t_request	= App\RequestModel::find($id);
			$idNomina	= $t_request->nominasReal->first()->idnomina;
			$rows_registered = 0;

			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{
					if ($request->realPath[$i] != "") 
					{
						$new_file_name					= Files::rename($request->realPath[$i],$t_request->folio);
						$documents						= new App\NominaDocuments();
						$documents->path				= $new_file_name;
						$documents->idnominaEmployee	= $request->idnominaEmployee[$i];
						$documents->name				= $request->nameDocument[$i];
						$documents->users_id			= Auth::user()->id;
						$documents->save();
						$rows_registered++;
					}
				}
				if ($rows_registered == 0) 
				{
					$alert	= "swal('','".Lang::get("messages.file_null")."', 'error');";
				}
				else
				{
					$alert	= "swal('','".Lang::get("messages.files_updated")."', 'success');";
				}
			}
			else
			{
				$alert	= "swal('','".Lang::get("messages.file_null")."', 'error');";
			}

			return redirect()->route('nomina.nomina-follow',['id'=>$id])->with('alert',$alert);
		}
		else
		{
			return redirect('error');
		}
	}

	public function validationEmployeeDocument(Request $request)
	{
		$checks			= ['CFDI PDF','CFDI XML'];
		$types			= ['CFDI PDF','CFDI XML'];
		$option			= '';
		$option 		.= '<option value="Comprobante de Transferencia">Comprobante de Transferencia</option>';
		$nominaEmployee	= App\NominaEmployee::find($request->idnominaEmployee);

		foreach ($nominaEmployee->documentsNom35 as $doc) 
		{
			if (($key = array_search($doc->name, $types)) !== false) 
			{
			    unset($types[$key]);
			}
		}

		for ($i=0; $i < count($checks); $i++) 
		{ 
			if(in_array($checks[$i],$types))
			{
				$option .= '<option value="'.$checks[$i].'">'.$checks[$i].'</option>';
			}
		}

		return Response($option);
	}

	public function downloadPayment($path)
	{
		if(\Storage::disk('public')->exists('/docs/nomina/'.$path))
		{
			return \Storage::disk('public')->download('/docs/nomina/'.$path);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function prenominaObra()
	{
		if(Auth::user()->module->where('id',307)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			return view('administracion.nomina.alta-prenomina-obra',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 307
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function getDataEmployeeObra(Request $request)
	{
		if ($request->ajax()) 
		{
			$employee = App\RealEmployee::find($request->employee_id);
			return view('administracion.nomina.partial.datos_prenomina_empleado',['employee'=>$employee]);
		}
	}

	public function massiveEmployeeObra(Request $request)
	{
		if ($request->ajax())
		{
			$response = array(
				'error'		=> 'ERROR',
				'message'	=> 'Error, por favor intente nuevamente'
			);
			if($request->file('csv_file')->isValid())
			{
				$extention	= strtolower($request->file('csv_file')->getClientOriginalExtension());
				if($extention == 'csv')
				{
					$csvArr		= array();
					if (($handle = fopen($request->file('csv_file'), "r")) !== FALSE)
					{
						$first	= true;
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
					$response['table']	= '';
					$selected			= 0;
					$total				= 0;
					$countIncidents 	= 0;
					$added				= array();

					$body		= [];
					$modelBody	= [];
					$modelHead	= [
						["value" => "Nombre del Empleado", "show" => "true"],
						["value" => "Faltas"],
						["value" => "Horas extra"],
						["value" => "Días festivos"],
						["value" => "Domingos trabajados"],
						["value" => "Acción"]
					];

					foreach ($csvArr as $key => $e)
					{
						$exist	= App\RealEmployee::where('curp',trim($e['curp']))
								->whereHas('workerDataVisible',function($query)
								{
									$query->whereIn('workerStatus',[1,2,3,4,5]);
									$query->whereIn('project',Auth::user()->inChargeProject(307)->pluck('project_id'));
								})
								->get();

						if(count($exist)>0)
						{
							$employee			= $exist->first();
							if(count($employee->workerDataVisible)>0 && ($employee->workerDataVisible->first()->workerStatus == 1 || $employee->workerDataVisible->first()->workerStatus == 2 || $employee->workerDataVisible->first()->workerStatus == 3)&& !in_array($employee->id, $added))
							{
								$periodicity	= $employee->workerDataVisible->first()->periodicity;
								$added[]		= $employee->id;
								$absence		= empty(trim($e['faltas'])) || !is_numeric($e['faltas']) || (int)$e['faltas'] < 0 ? '0' : round($e['faltas']);
								$extra_hours	= empty(trim($e['horas_extra'])) || !is_numeric($e['horas_extra']) || (int)$e['horas_extra'] < 0 ? '0' : round($e['horas_extra']);
								$holidays		= empty(trim($e['dias_festivos'])) || !is_numeric($e['dias_festivos']) || (int)$e['dias_festivos'] < 0 ? '0' : round($e['dias_festivos']);
								$sundays		= empty(trim($e['domingos_trabajados'])) || !is_numeric($e['domingos_trabajados']) || (int)$e['domingos_trabajados'] < 0 ? '0' : round($e['domingos_trabajados']);
								$flag			= true;
								
								if($periodicity == "01" && ($absence > 1 || $holidays > 1))
								{
									$flag = false;
									$countIncidents++;
								}
								elseif($periodicity == "02" && ($absence > 7 || $sundays > 1 || $holidays > 1))
								{
									$flag = false;
									$countIncidents++;
								}
								elseif($periodicity == "03" && ($absence > 14 || $sundays > 2 || $holidays > 2))
								{
									$flag = false;
									$countIncidents++;
								}
								elseif($periodicity == "04" && ($absence > 14 || $sundays > 2 || $holidays > 2))
								{
									$flag = false;
									$countIncidents++;
								}
								elseif($periodicity == "05" && ($absence > 30 || $sundays > 4 || $holidays > 4))
								{
									$flag = false;
									$countIncidents++;
								}
								elseif($periodicity == "")
								{
									$flag = false;
									$countIncidents++;
								}	
								
								if ($flag) 
								{
									$response['error']	= 'DONE';
										// $response['table']	.= 
									$body = [ "classEx"	=> "tr_bodypayroll",
										[
											"show" => "true",
											"content" =>
											[
												[
													"label" => $employee->orderedName()
												],
												[
													"kind"			=> "components.inputs.input-text",
													"attributeEx"	=> "type=\"hidden\" name=\"employee_id[]\" value=\"".$employee->id."\"",
													"classEx"		=> "idemployee-table-prenomina"
												]
											]
										],
										[
											"content" =>
											[
												"kind" 			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"text\" name=\"absence[]\" placeholder=\"Ingrese las faltas\" value=\"".$absence."\""
											]
										],
										[
											"content" =>
											[
												"kind" 			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"text\" name=\"extra_hours[]\" placeholder=\"Ingrese las horas extras\" value=\"".$extra_hours."\""
											]
										],
										[
											"content" =>
											[
												"kind" 			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"text\" name=\"holidays[]\" placeholder=\"Ingrese los días festivos\" value=\"".$holidays."\""
											]
										],
										[
											"content" =>
											[
												"kind" 			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"text\" name=\"sundays[]\" placeholder=\"Ingrese los domingos trabjados\" value=\"".$sundays."\""
											]
										],
										[
											"content" =>
											[
												"kind"		=> "components.buttons.button",
												"variant"	=> "red",
												"classEx"	=> "btn-delete-tr",
												"label"		=> "<span class=\"icon-x\"></span>"
											]
										]
									];
									$modelBody[] = $body;
									
									$selected++;
								}
								else
								{
									$response['curp'][] = $e['curp'];
								}
							}
							else
							{
								$response['curp'][] = $e['curp'];
							}
						}
						$total++;
					}
					$response['table'] .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table", [ 
						"modelBody"			=> $modelBody,
						"modelHead"			=> $modelHead,
						"noHead"			=> "true"
					])));
					if($selected == 0)
					{
						$response['message']	= 'Ningún empleado seleccionado, por favor asegúrese que los empleados contenidos en el archivo se encuentren dados de alta, que tengan un proyecto asignado y que se encuentren activos.';
					}
					else
					{
						if ($countIncidents > 0) 
						{
							$response['message']	= 'Empleados seleccionados: '.$selected.' de un total de '.$total.' líneas en su CSV. Por favor verifique las faltas, domingos, trabajados y días festivos de los empleados.';
						}
						else
						{
							$response['message']	= 'Empleados seleccionados: '.$selected.' de un total de '.$total.' líneas en su CSV. Verifique se formen parte del proyecto y que se encuentren activos”';
						}
					}
				}
				else
				{
					$response['message']	= 'Archivo inválido, el archivo debe ser en formato CSV';
				}
			}
			else
			{
				$response['message']	= 'Archivo inválido, por favor intente de nuevo';
			}
			return response($response);
		}
	}

	public function storePrenominaObra(Request $request)
	{
		if(Auth::user()->module->where('id',307)->count()>0)
		{
			if(isset($request->prenomina_id))
			{
				$prenomina			= App\Prenomina::find($request->prenomina_id);
				if ($prenomina->status == 1) 
				{
					$alert	= "swal('', 'La prenómina ya fue enviada anteriormente.', 'info');";
					return redirect()->route('nomina.index')->with('alert',$alert);
				}
				elseif ($prenomina->status == 3) 
				{
					$alert	= "swal('', 'La prenómina ya escuentra eliminada.', 'info');";
					return redirect()->route('nomina.index')->with('alert',$alert);
				}
				else
				{
					$prenomina->title		= $request->title;
					$prenomina->datetitle 	= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
					$prenomina->status		= 1;
					$prenomina->user_id		= Auth::user()->id;
					$prenomina->project_id	= $request->project_id;
					$prenomina->save();
				}

				$prenomina->employee()->detach();
			}

			if(!isset($request->prenomina_id))
			{
				$now = Carbon::now();
				$prenomina						= new App\Prenomina();
				$prenomina->title				= $request->title;
				$prenomina->datetitle 			= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
				$prenomina->date				= $now;
				$prenomina->idCatTypePayroll	= $request->type_payroll;
				$prenomina->kind				= 1;
				$prenomina->status				= 1;
				$prenomina->project_id 			= $request->project_id;
				$prenomina->user_id 			= Auth::user()->id;
				$prenomina->save();
			}

			if (isset($request->employee_id) && count($request->employee_id)>0) 
			{
				for ($i=0; $i < count($request->employee_id); $i++) 
				{ 
					DB::table('prenomina_employee')->insert(
					[
					    [
							'idreal_employee'	=> $request->employee_id[$i],
							'absence'			=> $request->absence[$i],
							'extra_hours'		=> $request->extra_hours[$i],
							'holidays'			=> $request->holidays[$i],
							'sundays'			=> $request->sundays[$i],
							'idprenomina'		=> $prenomina->idprenomina
					    ],
					]);
				}
			}

			$alert = 'swal("","Prenómina creada exitosamente","success");';
			return redirect('administration/nomina')->with('alert',$alert);
		}
	}

	public function savePrenominaObra(Request $request)
	{
		if(Auth::user()->module->where('id',307)->count()>0)
		{
			if(isset($request->prenomina_id))
			{
				$prenomina			= App\Prenomina::find($request->prenomina_id);
				if ($prenomina->status == 1) 
				{
					$alert	= "swal('', 'La prenómina ya fue enviada anteriormente.', 'info');";
					return redirect()->route('nomina.index')->with('alert',$alert);
				}
				elseif ($prenomina->status == 3) 
				{
					$alert	= "swal('', 'La prenómina ya escuentra eliminada.', 'info');";
					return redirect()->route('nomina.index')->with('alert',$alert);
				}
				else
				{
					$prenomina->title		= $request->title;
					$prenomina->datetitle	= $request->datetitle != '' ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
					$prenomina->status		= 0;
					$prenomina->user_id		= Auth::user()->id;
					$prenomina->project_id	= $request->project_id;
					$prenomina->save();
				}

				$prenomina->employee()->detach();
			}

			if(!isset($request->prenomina_id))
			{
				$now = Carbon::now();
				$prenomina						= new App\Prenomina();
				$prenomina->title				= $request->title;
				$prenomina->datetitle			= $request->datetitle != '' ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
				$prenomina->date				= $now;
				$prenomina->idCatTypePayroll	= $request->type_payroll;
				$prenomina->kind				= 1;
				$prenomina->status				= 0;
				$prenomina->project_id 			= $request->project_id;
				$prenomina->user_id 			= Auth::user()->id;
				$prenomina->save();
			}

			if (isset($request->employee_id) && count($request->employee_id)>0) 
			{
				for ($i=0; $i < count($request->employee_id); $i++) 
				{ 
					DB::table('prenomina_employee')->insert(
					[
					    [
							'idreal_employee'	=> $request->employee_id[$i],
							'absence'			=> $request->absence[$i],
							'extra_hours'		=> $request->extra_hours[$i],
							'holidays'			=> $request->holidays[$i],
							'sundays'			=> $request->sundays[$i],
							'idprenomina'		=> $prenomina->idprenomina
					    ],
					]);
				}
			}

			$alert = 'swal("","Prenómina guardada exitosamente","success");';
			return redirect()->route('nomina.prenomina-obra-edit',$prenomina->idprenomina)->with('alert',$alert);
		}
	}

	public function prenominaObraEdit(App\Prenomina $prenomina)
	{
		if(Auth::user()->module->where('id',307)->count()>0)
		{
			if ($prenomina->status != 3) 
			{
				$data = App\Module::find($this->module_id);
				return view('administracion.nomina.alta-prenomina-obra',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 307,
						'prenomina'	=> $prenomina
					]);
			}
			else
			{
				$alert	= "swal('', 'La prenómina ya escuentra eliminada.', 'info');";
				return redirect()->route('nomina.index')->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function prenominaObraDelete(App\Prenomina $prenomina)
	{
		if(Auth::user()->module->where('id',307)->count()>0)
		{
			if ($prenomina->status == 1 || $prenomina->status == 2) 
			{
				$alert	= "swal('', 'La prenómina ya fue enviada anteriormente.', 'info');";
				return redirect()->route('nomina.index')->with('alert',$alert);
			}
			elseif ($prenomina->status == 3) 
			{
				$alert	= "swal('', 'La prenómina ya escuentra eliminada.', 'info');";
				return redirect()->route('nomina.index')->with('alert',$alert);
			}
			else
			{
				$prenomina->user_id	= Auth::user()->id;
				$prenomina->status	= 3;
				$prenomina->save();
				$alert	= "swal('', 'Prenómina eliminada exitosamente', 'success');";
				return redirect()->route('nomina.index')->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function prenominaObraDownload(App\Prenomina $prenomina)
	{
		if (Auth::user()->module->where('id',307)->count() > 0) 
		{
			Excel::create('Prenomina '.$prenomina->title, function($excel) use ($prenomina)
			{
				$excel->sheet('Empleados',function($sheet) use($prenomina)
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

					$sheet->cell('A1:F1', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});

					$sheet->row(1,['curp','nombre','faltas','horas_extra','dias_festivos','domingos_trabajados']);
					foreach($prenomina->employee->sortBy(function($employee){ return $employee->last_name.' '.$employee->scnd_last_name.' '.$employee->name; }) as $emp)
					{
						$row = [];
						$row[] = $emp->curp;
						$row[] = $emp->orderedName();
						$row[] = '';
						$row[] = '';
						$row[] = '';
						$row[] = '';
						$sheet->appendRow($row);
					}
				});
			})->export('csv');
		}
		else
		{
			return redirect('/');
		}
	}

	public function exportLayoutFiscal(App\RequestModel $request_model,Request $request)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count() > 0) 
		{
			$selectRaw 		= '
								nomina_employees.idnominaEmployee as idnominaEmployee,
								real_employees.curp as curp,
								CONCAT_WS(" ", real_employees.last_name, real_employees.scnd_last_name, real_employees.name) as name,
								IF(nomina_employees.idCatPeriodicity IS NOT NULL, nomina_employees.idCatPeriodicity, worker_datas.periodicity) as periodicity,
								IF(nomina_employees.absence IS NOT NULL, nomina_employees.absence, 0) as absence,
								IF(nomina_employees.extra_hours IS NOT NULL, nomina_employees.extra_hours, 0) as extra_hours,
								IF(nomina_employees.holidays IS NOT NULL, nomina_employees.holidays, 0) as holidays,
								IF(nomina_employees.sundays IS NOT NULL, nomina_employees.sundays, 0) as sundays
							';
			$nominaEmployees = DB::table('request_models')
							->selectRaw($selectRaw)
							->leftJoin('nominas','nominas.idFolio','request_models.folio')
							->leftJoin('nomina_employees','nomina_employees.idnomina','nominas.idnomina')
							->leftJoin('real_employees','real_employees.id','nomina_employees.idrealEmployee')
							->leftJoin('worker_datas','worker_datas.id','nomina_employees.idworkingData')
							->where('request_models.folio',$request_model->folio)
							->where('nomina_employees.visible',1)
							->orderBy('real_employees.last_name','ASC')
							->orderBy('real_employees.scnd_last_name','ASC')
							->orderBy('real_employees.name','ASC')
							->get();

			$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat	= (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('ffffff')->setFontColor(Color::BLACK)->setFontBold()->build();
			$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('EE881F')->setFontColor(Color::WHITE)->setFontBold()->build();
			$writer			= WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Plantilla -'.$request_model->nominasReal->first()->title.'.xlsx');
			$writer->getCurrentSheet()->setName('Empleados');
			$headerArray	= ['id','curp','nombre','periodicidad','faltas','horas_extra','dias_festivos','domingos_trabajados'];
			$tempHeaders		= [];
			foreach($headerArray as $k => $header)
			{
				if($k <= 3)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				else
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$tempIdrealEmployee	= '';
			$kindRow			= true;
			$flagAlimony 		= false;
			foreach($nominaEmployees as $nomina_employee)
			{
				if($tempIdrealEmployee != $nomina_employee->idnominaEmployee)
				{
					$tempIdrealEmployee = $nomina_employee->idnominaEmployee;
					$kindRow = !$kindRow;
					
				}
				$tmpArr = [];
				foreach($nomina_employee as $k => $n)
				{
					$tmpArr[] = WriterEntityFactory::createCell($n);
				}
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr);
				}
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
		}
		else
		{
			return redirect('/error');
		}
	}

	public function uploadLayoutFiscal(App\RequestModel $request_model,Request $request)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count() > 0) 
		{
			if(isset($request) && $request->csv_file != "" && $request->file('csv_file')->isValid() && $request->file('csv_file')->getClientOriginalExtension() == "csv")
			{
				$name		= '/update_nomina/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
				\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
				$path		= \Storage::disk('reserved')->path($name);
				$array_data		= array();
				if (($handle = fopen($path, "r")) !== FALSE)
				{
					$first	= true;
					while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
					{
						if($first)
						{
							$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
							$first		= false;
						}
						$array_data[]	= $data;
					}
					fclose($handle);
				}
				array_walk($array_data, function(&$a) use ($array_data)
				{
					$a = array_combine($array_data[0], $a);
				});
				array_shift($array_data);

				$headers = 
				[
					'id','curp','nombre','periodicidad','faltas','horas_extra','dias_festivos','domingos_trabajados'
				];

				if(empty($array_data) || array_diff($headers, array_keys($array_data[0])))
				{
					$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
					return back()->with('alert',$alert);	
				}

				$countRows = $errors = $updated = 0;
				foreach ($array_data as $data) 
				{
					$flag = true;
					if ((isset($data['faltas']) && trim($data['faltas'] != "")) &&
						(isset($data['horas_extra']) && trim($data['horas_extra'] != "")) &&
						(isset($data['dias_festivos']) && trim($data['dias_festivos'] != "")) &&
						(isset($data['domingos_trabajados']) && trim($data['domingos_trabajados'] != "")) &&
						(isset($data['periodicidad']) && trim($data['periodicidad'] != "") && in_array($data['periodicidad'],['01','02','03','04','05'])))
					{
						
						$t_nominaemployee	= App\NominaEmployee::find($data['id']);
						if ($t_nominaemployee != "") 
						{
							$periodicity = $data['periodicidad'];

							if($periodicity == "01" && ($data['faltas'] > 1 || $data['dias_festivos'] > 1  || $data['horas_extra'] < 0 || $data['faltas'] < 0 || $data['domingos_trabajados'] < 0 || $data['dias_festivos'] < 0))
							{
								$flag = false;
							}
							elseif($periodicity == "02" && ($data['faltas'] > 7 || $data['domingos_trabajados'] > 1 || $data['dias_festivos'] > 1  || $data['horas_extra'] < 0 || $data['faltas'] < 0 || $data['domingos_trabajados'] < 0 || $data['dias_festivos'] < 0))
							{
								$flag = false;
							}
							elseif($periodicity == "03" && ($data['faltas'] > 14 || $data['domingos_trabajados'] > 2 || $data['dias_festivos'] > 2  || $data['horas_extra'] < 0 || $data['faltas'] < 0 || $data['domingos_trabajados'] < 0 || $data['dias_festivos'] < 0))
							{
								$flag = false;
							}
							elseif($periodicity == "04" && ($data['faltas'] > 14 || $data['domingos_trabajados'] > 2 || $data['dias_festivos'] > 2  || $data['horas_extra'] < 0 || $data['faltas'] < 0 || $data['domingos_trabajados'] < 0 || $data['dias_festivos'] < 0))
							{
								$flag = false;
							}
							elseif($periodicity == "05" && ($data['faltas'] > 30 || $data['domingos_trabajados'] > 4 || $data['dias_festivos'] > 4  || $data['horas_extra'] < 0 || $data['faltas'] < 0 || $data['domingos_trabajados'] < 0 || $data['dias_festivos'] < 0))
							{
								$flag = false;
							}
							elseif($periodicity == "")
							{
								$flag = false;
							}

							if ($flag) 
							{
								$t_nominaemployee->from_date        = $request->from_date_request	!= "" ? Carbon::createFromFormat('d-m-Y',$request->from_date_request)->format('Y-m-d')	: null;
								$t_nominaemployee->to_date          = $request->to_date_request		!= "" ? Carbon::createFromFormat('d-m-Y',$request->to_date_request)->format('Y-m-d') 	: null;
								$t_nominaemployee->absence          = $data['faltas'];
								$t_nominaemployee->extra_hours      = $data['horas_extra'];
								$t_nominaemployee->holidays         = $data['dias_festivos'];
								$t_nominaemployee->sundays          = $data['domingos_trabajados'];
								$t_nominaemployee->save();
								$updated++;
							}
							else
							{
								$errors++;
							}
						}
						else
						{
							$errors++;
						}
					}
					else
					{
						$errors++;
					}
					$countRows++;
				}
				if ($errors > 0) 
				{
					$alert 	= "swal('', 'Registros actualizados: ".$updated." de un total de ".$countRows." líneas en su CSV. Verifique que las faltas, días trabajados y domingos trabajados, no se excedan a la periodicidad de cada empleado.', 'info');";
				}
				else
				{
					$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
				}
				return redirect()->route('nomina.nomina-create',['id'=>$request_model->folio])->with('alert',$alert);
			}
			$alert	= "swal('', '".Lang::get("messages.extension_allowed",["param" => 'CSV'])."', 'error');";
			return redirect()->route('nomina.nomina-create',['id'=>$request_model->folio])->with('alert',$alert);
		}
	}

	public function exportLayoutNF(App\RequestModel $request_model,Request $request)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count() > 0) 
		{
			Excel::create('Plantilla -'.$request_model->nominasReal->first()->title, function($excel) use ($request_model)
			{
				$excel->sheet('Empleados',function($sheet) use($request_model)
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

					$sheet->cell('A1:AD1', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});

					$check_request_fiscal 	= App\RequestModel::where('kind',16)
						->where('idprenomina',$request_model->idprenomina)
						->where('idDepartment',$request_model->idDepartment)
						->where('taxPayment',1)
						->first();

					if ($request_model->nominasReal->first()->idCatTypePayroll == '001') 
					{
						if($check_request_fiscal != '')
						{
							$sheet->cell('G1:J1', function($cells)
							{
								$cells->setBackground('#EE881F');
								$cells->setFontColor('#ffffff');
							});

							$sheet->cell('Y1:AD1', function($cells)
							{
								$cells->setBackground('#EE881F');
								$cells->setFontColor('#ffffff');
							});

							$sheet->cell('K1:K1', function($cells)
							{
								$cells->setBackground('#54A935');
								$cells->setFontColor('#ffffff');
							});

							$sheet->row(1,
							[
								'id',
								'curp',
								'nombre',
								'periodicidad',
								'sd_real',
								'dias_trabajados',
								'faltas',
								'horas_extra',
								'dias_festivos',
								'domingos_trabajados',
								'sueldo_real',
								'total_horas_extra',
								'total_dias_festivos',
								'total_domingos_trabajados',
								'total_a_pagar',
								'horas_extra_fiscal',
								'dias_festivos_fiscal',
								'domingos_trabajados_fiscal',
								'neto_fiscal',
								'total_fiscal_pagado',
								'infonavit_fiscal',
								'fonacot_fiscal',
								'pension_alimenticia',
								'prestamo_fiscal',
								'infonavit_complemento_no_fiscal', // T
								'horas_extra_no_fiscal',
								'dias_festivos_no_fiscal',
								'domingos_trabajados_no_fiscal',
								'neto_no_fiscal',
								'total_no_fiscal_por_pagar' // Y
							]);	
						}
						else
						{
							$sheet->cell('G1:J1', function($cells)
							{
								$cells->setBackground('#EE881F');
								$cells->setFontColor('#ffffff');
							});

							$sheet->cell('P1:T1', function($cells)
							{
								$cells->setBackground('#EE881F');
								$cells->setFontColor('#ffffff');
							});

							$sheet->cell('K1:K1', function($cells)
							{
								$cells->setBackground('#54A935');
								$cells->setFontColor('#ffffff');
							});

							$sheet->row(1,
							[
								'id',
								'curp',
								'nombre',
								'periodicidad',
								'sd_real',
								'dias_trabajados',
								'faltas',
								'horas_extra',
								'dias_festivos',
								'domingos_trabajados',
								'sueldo_real',
								'total_horas_extra',
								'total_dias_festivos',
								'total_domingos_trabajados',
								'total_a_pagar',
								'horas_extra_no_fiscal', // O
								'dias_festivos_no_fiscal',
								'domingos_trabajados_no_fiscal',
								'neto_no_fiscal',
								'total_no_fiscal_por_pagar' // S
							]);
						}
					}
					else
					{
						$sheet->cell('D1:D1', function($cells)
							{
								$cells->setBackground('#EE881F');
								$cells->setFontColor('#ffffff');
							});

						$sheet->row(1,
						[
							'id',
							'curp',
							'nombre',
							'total_no_fiscal_por_pagar' // D
						]);
					}

					foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request_model->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
					{
						if($check_request_fiscal != '')
						{
							$nominaemp = App\NominaEmployee::where('idrealEmployee',$n->idrealEmployee)
								->where('idnomina',$check_request_fiscal->nominasReal->first()->idnomina)
								->first();
						}
						else
						{
							$nominaemp = "";
						}
						$sueldo_fiscal				= 0;
						$infonavit_fiscal			= 0;
						$infonavit_complemento		= 0;
						$fonacot_fiscal				= 0;
						$total_extra_time_fiscal	= 0;
						$total_sundays_fiscal		= 0;
						$total_holiday_fiscal		= 0;
						$total_extra_time_no_fiscal	= 0;
						$total_sundays_no_fiscal	= 0;
						$total_holiday_no_fiscal	= 0;
						$sueldo_total_fiscal  		= 0;
						$alimony_fiscal 			= 0;
						$loan_retention_fiscal 		= 0;
						$total_fiscal_pagado 		= $total_extra_time_fiscal + $total_holiday_fiscal + $total_sundays_fiscal + $sueldo_total_fiscal;
						$total_neto					= $n->workerData->first()->netIncome;
						$worked_days				= $nominaemp != "" && $nominaemp->idCatPeriodicity != "" ? App\CatPeriodicity::find($nominaemp->idCatPeriodicity)->days : ($n->workerData->first()->periodicity != "" ? App\CatPeriodicity::find($n->workerData->first()->periodicity)->days : 1);
						$sd_real					= round($total_neto/$worked_days,6);
						$horas_extra				= $n->extra_hours;
						$total_extra_time_real 		= $horas_extra < 9 ? round(($sd_real/8)*2*$horas_extra,2) : round((($sd_real/8)*2*9)+(($sd_real/8)*3)*($horas_extra-9),2);
						$total_extra_time_no_fiscal = $n->nominasEmployeeNF()->exists() && $n->nominasEmployeeNF->first()->extra_time != "" ? round($n->nominasEmployeeNF->first()->extra_time,2) : ($total_extra_time_real - $total_extra_time_fiscal);
						$dias_festivo				= $n->holidays;
						$total_dias_festivo_real	= round($dias_festivo*$sd_real*2,2);
						$total_holiday_no_fiscal	= $n->nominasEmployeeNF()->exists() && $n->nominasEmployeeNF->first()->holiday != "" ? round($n->nominasEmployeeNF->first()->holiday,2) : ($total_dias_festivo_real - $total_holiday_fiscal);
						$domingos					= $n->sundays;
						$total_sundays_real			= round(($sd_real*1.25)*$domingos,2);
						$total_sundays_no_fiscal	= $n->nominasEmployeeNF()->exists() && $n->nominasEmployeeNF->first()->sundays != "" ? round($n->nominasEmployeeNF->first()->sundays,2) : ($total_sundays_real - $total_sundays_fiscal);
						$sueldo_real				= round($sd_real*($worked_days-$n->absence),2);
						$sueldo_total_no_fiscal 	= $n->nominasEmployeeNF()->exists() && $n->nominasEmployeeNF->first()->complementPartial != "" ? $n->nominasEmployeeNF->first()->complementPartial : round($sueldo_real-$sueldo_total_fiscal,2);
						$total_no_fiscal_por_pagar 	= round($total_extra_time_no_fiscal + $total_holiday_no_fiscal + $total_sundays_no_fiscal + $sueldo_total_no_fiscal - $infonavit_complemento,2);
						$request_netIncome 			= $n->workerData->first()->complement;
						if ($nominaemp != '' || $nominaemp != null) 
						{
							if ($nominaemp->salary()->exists()) 
							{
								$sueldo_fiscal				= $nominaemp->salary->first()->netIncome;
								$infonavit_fiscal			= $nominaemp->salary->first()->infonavit != '' ? $nominaemp->salary->first()->infonavit : 0;
								$infonavit_complemento		= $nominaemp->salary->first()->infonavitComplement != '' ? $nominaemp->salary->first()->infonavitComplement : 0;
								$fonacot_fiscal				= $nominaemp->salary->first()->fonacot != '' ? $nominaemp->salary->first()->fonacot : 0;
								$alimony_fiscal 			= $nominaemp->salary->first()->alimony != '' ? $nominaemp->salary->first()->alimony : 0;
								$loan_retention_fiscal 		= $nominaemp->salary->first()->loan_retention != '' ? $nominaemp->salary->first()->loan_retention : 0;
								$request_netIncome			= $n->workerData->first()->netIncome != '' && $n->workerData->first()->netIncome>$sueldo_fiscal ? $n->workerData->first()->netIncome : $sueldo_fiscal;

								$total_extra_time_fiscal	= round($nominaemp->salary->first()->extra_time,2);
								$total_sundays_fiscal		= round($nominaemp->salary->first()->exempt_sunday,2) + round($nominaemp->salary->first()->taxed_sunday,2);
								$total_holiday_fiscal		= round($nominaemp->salary->first()->holiday,2);
								$sueldo_total_fiscal		= round($sueldo_fiscal-$total_extra_time_fiscal-$total_holiday_fiscal-$total_sundays_fiscal,2);
								$total_fiscal_pagado 		= $total_extra_time_fiscal + $total_holiday_fiscal + $total_sundays_fiscal + $sueldo_total_fiscal;

								$total_neto					= $n->workerData->first()->netIncome;
								$worked_days				= $nominaemp != "" && $nominaemp->idCatPeriodicity != "" ? App\CatPeriodicity::find($nominaemp->idCatPeriodicity)->days : ($n->workerData->first()->periodicity != "" ? App\CatPeriodicity::find($n->workerData->first()->periodicity)->days : 1);
								$sd_real					= round($total_neto/$worked_days,6);

								$worked_days 				= $nominaemp->salary->first()->workedDays;
								$horas_extra				= $n->extra_hours;
								$total_extra_time_real 		= $horas_extra < 9 ? round(($sd_real/8)*2*$horas_extra,2) : round((($sd_real/8)*2*9)+(($sd_real/8)*3)*($horas_extra-9),2);
								$total_extra_time_no_fiscal = $n->nominasEmployeeNF()->exists() && $n->nominasEmployeeNF->first()->extra_time != "" ? round($n->nominasEmployeeNF->first()->extra_time,2) : ($total_extra_time_real - $total_extra_time_fiscal);

								$dias_festivo				= $n->holidays;
								$total_dias_festivo_real	= round($dias_festivo*$sd_real*2,2);
								$total_holiday_no_fiscal	= $n->nominasEmployeeNF()->exists() && $n->nominasEmployeeNF->first()->holiday != "" ? round($n->nominasEmployeeNF->first()->holiday,2) : ($total_dias_festivo_real - $total_holiday_fiscal);

								$domingos					= $n->sundays;
								$total_sundays_real			= round(($sd_real*1.25)*$domingos,2);
								$total_sundays_no_fiscal	= $n->nominasEmployeeNF()->exists() && $n->nominasEmployeeNF->first()->sundays != "" ? round($n->nominasEmployeeNF->first()->sundays,2) : ($total_sundays_real - $total_sundays_fiscal);

								$sueldo_real				= round($sd_real*($worked_days),2);
								$sueldo_total_no_fiscal 	= $n->nominasEmployeeNF()->exists() && $n->nominasEmployeeNF->first()->complementPartial != "" ? round($n->nominasEmployeeNF->first()->complementPartial,2) : round($sueldo_real-($sueldo_total_fiscal+$infonavit_fiscal+$fonacot_fiscal+$alimony_fiscal+$loan_retention_fiscal),2);

								$total_no_fiscal_por_pagar 	= round($total_extra_time_no_fiscal + $total_holiday_no_fiscal + $total_sundays_no_fiscal + $sueldo_total_no_fiscal - $infonavit_complemento,2);
							}
							elseif ($nominaemp->bonus()->exists())
							{
								$sueldo_fiscal		= $nominaemp->bonus->first()->netIncome;
								$request_netIncome	= $nominaemp->bonus->first()->totalIncomeBonus;
							}
							elseif ($nominaemp->liquidation()->exists()) 
							{
								$sueldo_fiscal		= $nominaemp->liquidation->first()->netIncome;
								$request_netIncome	= $nominaemp->liquidation->first()->totalIncomeLiquidation;
							}
							elseif ($nominaemp->vacationPremium()->exists()) 
							{
								$sueldo_fiscal		= $nominaemp->vacationPremium->first()->netIncome;
								$request_netIncome	= $nominaemp->vacationpremium->first()->totalIncomeVP;
							}
							elseif ($nominaemp->profitSharing()->exists()) 
							{
								$sueldo_fiscal		= $nominaemp->profitSharing->first()->netIncome;
								$request_netIncome	= $nominaemp->profitSharing->first()->totalIncomePS;
							}
						}

						$row	= [];
						$row[]	= $n->idnominaEmployee;
						$row[]	= $n->employee->first()->curp;
						$row[]	= $n->employee->first()->orderedName();

						if ($request_model->nominasReal->first()->idCatTypePayroll == '001') 
						{
							$row[] 	= $n->idCatPeriodicity != "" ? $n->idCatPeriodicity : $n->workerData->first()->periodicity;
							$row[]	= $sd_real;
							$row[]	= $worked_days;
							$row[]	= $n->absence != "" ? $n->absence : '0';
							$row[]	= $n->extra_hours != "" ? $n->extra_hours : '0';
							$row[]	= $n->holidays != "" ? $n->holidays : '0';
							$row[]	= $n->sundays != "" ? $n->sundays : '0';
							$row[]	= $sueldo_real;
							$row[]	= $request_model->status == 2 ? $total_extra_time_fiscal + $total_extra_time_no_fiscal : $n->nominasEmployeeNF->first()->extra_time;
							$row[]	= $request_model->status == 2 ? $total_holiday_fiscal + $total_holiday_no_fiscal : $n->nominasEmployeeNF->first()->holiday;
							$row[]	= $request_model->status == 2 ? $total_sundays_fiscal + $total_sundays_no_fiscal : $n->nominasEmployeeNF->first()->sundays;
							$row[]	= $request_model->status == 2 ? $sueldo_real + $total_extra_time_fiscal + $total_extra_time_no_fiscal + $total_holiday_fiscal + $total_holiday_no_fiscal + $total_sundays_fiscal + $total_sundays_no_fiscal : $n->nominasEmployeeNF->first()->amount + $total_fiscal_pagado;

							if($check_request_fiscal != '')
							{	
								$row[]	= $total_extra_time_fiscal;
								$row[]	= $total_holiday_fiscal;
								$row[]	= $total_sundays_fiscal;
								$row[]	= $sueldo_total_fiscal;
								$row[]	= $total_fiscal_pagado;
								$row[]	= $infonavit_fiscal;
								$row[]	= $fonacot_fiscal;
								$row[]	= $alimony_fiscal;
								$row[]	= $loan_retention_fiscal;
								$row[]	= $infonavit_complemento;
							}

							$row[]	= $total_extra_time_no_fiscal;
							$row[]	= $total_holiday_no_fiscal;
							$row[]	= $total_sundays_no_fiscal;
							$row[]	= $sueldo_total_no_fiscal;
						}

						$row[] = $total_no_fiscal_por_pagar;
						$sheet->appendRow($row);
					}
				});
			})->export('xlsx');
		}
		else
		{
			return redirect('/error');
		}
	}

	public function uploadLayout(App\RequestModel $request_model,Request $request)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count() > 0) 
		{
			if(isset($request) && $request->csv_file != "" && $request->file('csv_file')->isValid() && $request->file('csv_file')->getClientOriginalExtension() == "csv")
			{
				$name		= '/update_nomina/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
				\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
				$path		= \Storage::disk('reserved')->path($name);
				$array_data		= array();
				if (($handle = fopen($path, "r")) !== FALSE)
				{
					$first	= true;
					while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
					{
						if($first)
						{
							$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
							$first		= false;
						}
						$array_data[]	= $data;
					}
					fclose($handle);
				}
				array_walk($array_data, function(&$a) use ($array_data)
				{
					$a = array_combine($array_data[0], $a);
				});
				array_shift($array_data);

				$check_request_fiscal 	= App\RequestModel::where('kind',16)
											->where('idprenomina',$request_model->idprenomina)
											->where('idDepartment',$request_model->idDepartment)
											->where('taxPayment',1)
											->first();

				$headers = 
				[
					'infonavit_complemento_no_fiscal','horas_extra_no_fiscal','dias_festivos_no_fiscal','domingos_trabajados_no_fiscal','neto_no_fiscal','total_no_fiscal_por_pagar','periodicidad'
				];

				if(empty($array_data) || array_diff($headers, array_keys($array_data[0])))
				{
					$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
					return back()->with('alert',$alert);	
				}

				$countRows = $errors = $updated = 0;
				foreach ($array_data as $data) 
				{
					$flag = true;
					if ((isset($data['infonavit_complemento_no_fiscal']) && trim($data['infonavit_complemento_no_fiscal'] != "")) &&
						(isset($data['horas_extra_no_fiscal']) && trim($data['horas_extra_no_fiscal'] != "")) &&
						(isset($data['dias_festivos_no_fiscal']) && trim($data['dias_festivos_no_fiscal'] != "")) &&
						(isset($data['domingos_trabajados_no_fiscal']) && trim($data['domingos_trabajados_no_fiscal'] != "")) &&
						(isset($data['neto_no_fiscal']) && trim($data['neto_no_fiscal'] != "")) &&
						(isset($data['total_no_fiscal_por_pagar']) && trim($data['total_no_fiscal_por_pagar'] != "")) &&
						(isset($data['periodicidad']) && in_array($data['periodicidad'],['01','02','03','04','05'] )))
					{
						$periodicity = $data['periodicidad'];

						if($periodicity == "01" && ($data['faltas'] > 1 || $data['dias_festivos'] > 1 || $data['faltas'] < 0 || $data['dias_festivos'] < 0 || $data['horas_extra'] < 0))
						{
							$flag = false;
						}
						elseif($periodicity == "02" && ($data['faltas'] > 7 || $data['domingos_trabajados'] > 1 || $data['dias_festivos'] > 1 || $data['horas_extra'] < 0 || $data['faltas'] < 0 || $data['domingos_trabajados'] < 0 || $data['dias_festivos'] < 0))
						{
							$flag = false;
						}
						elseif($periodicity == "03" && ($data['faltas'] > 14 || $data['domingos_trabajados'] > 2 || $data['dias_festivos'] > 2 || $data['horas_extra'] < 0 || $data['faltas'] < 0 || $data['domingos_trabajados'] < 0 || $data['dias_festivos'] < 0))
						{
							$flag = false;
						}
						elseif($periodicity == "04" && ($data['faltas'] > 14 || $data['domingos_trabajados'] > 2 || $data['dias_festivos'] > 2 || $data['horas_extra'] < 0 || $data['faltas'] < 0 || $data['domingos_trabajados'] < 0 || $data['dias_festivos'] < 0))
						{
							$flag = false;
						}
						elseif($periodicity == "05" && ($data['faltas'] > 30 || $data['domingos_trabajados'] > 4 || $data['dias_festivos'] > 4 ||  $data['horas_extra'] < 0 || $data['faltas'] < 0 || $data['domingos_trabajados'] < 0 || $data['dias_festivos'] < 0))
						{
							$flag = false;
						}
						elseif($periodicity == "")
						{
							$flag = false;
						}

						if ($flag) 
						{
							$t_nominaemployee                   = App\NominaEmployee::find($data['id']);
							if ($t_nominaemployee != "") 
							{
								$t_nominaemployee->from_date        = $request->from_date_request	!= "" ? Carbon::createFromFormat('d-m-Y',$request->from_date_request)->format('Y-m-d')	: null;
								$t_nominaemployee->to_date          = $request->to_date_request		!= "" ? Carbon::createFromFormat('d-m-Y',$request->to_date_request)->format('Y-m-d') 	: null;
								if ($check_request_fiscal == "") 
								{
									$t_nominaemployee->absence          = $data['faltas'];
									$t_nominaemployee->extra_hours      = $data['horas_extra'];
									$t_nominaemployee->holidays         = $data['dias_festivos'];
									$t_nominaemployee->sundays          = $data['domingos_trabajados'];
								}
								$t_nominaemployee->save();

								if ($t_nominaemployee->nominasEmployeeNF()->exists()) 
								{
									$t_nominaemployeenf		= App\NominaEmployeeNF::find($t_nominaemployee->nominasEmployeeNF->first()->idnominaemployeenf);
									App\DiscountsNomina::where('idnominaemployeenf',$t_nominaemployee->nominasEmployeeNF->first()->idnominaemployeenf)->delete();
								}
								else
								{
									$t_nominaemployeenf 	= new App\NominaEmployeeNF();
								}
								$t_nominaemployeenf->idnominaEmployee = $data['id'];

								if ($request_model->nominasReal->first()->idCatTypePayroll == '001') 
								{
									$t_nominaemployeenf->extra_time			= $data['horas_extra_no_fiscal'];
									$t_nominaemployeenf->holiday			= $data['dias_festivos_no_fiscal'];
									$t_nominaemployeenf->sundays			= $data['domingos_trabajados_no_fiscal'];
									$t_nominaemployeenf->complementPartial	= $data['neto_no_fiscal'];
									$t_nominaemployeenf->amount				= $data['total_no_fiscal_por_pagar'];
									$t_nominaemployeenf->netIncome    		= $data['total_fiscal_pagado'] + $data['total_no_fiscal_por_pagar'];
									$t_nominaemployeenf->save();

									if ($data['infonavit_complemento_no_fiscal'] > 0) 
									{
										$t_discount                     = new App\DiscountsNomina();
										$t_discount->amount             = $data['infonavit_complemento_no_fiscal'];
										$t_discount->reason             = 'INFONAVIT parte fiscal';
										$t_discount->idnominaemployeenf = $t_nominaemployeenf->idnominaemployeenf;
										$t_discount->save();
									}
								}
								$updated++;
							}
							else
							{
								$errors++;
							}
						}
						else
						{
							$errors++;
						}
					}
					else
					{
						$errors++;
					}
					$countRows++;
				}
				if ($errors > 0) 
				{
					$alert 	= "swal('', 'Registros actualizados: ".$updated." de un total de ".$countRows." líneas en su CSV. Verifique que las faltas, días trabajados y domingos trabajados, no se excedan a la periodicidad de cada empleado..', 'info');";
				}
				else
				{
					$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
				}
				return redirect()->route('nomina.nomina-create',['id'=>$request_model->folio])->with('alert',$alert);
			}
			$alert	= "swal('', '".Lang::get("messages.extension_allowed",["param" => 'CSV'])."', 'error');";
			return redirect()->route('nomina.nomina-create',['id'=>$request_model->folio])->with('alert',$alert);
		}
	}

	public function reportNom035(App\RequestModel $request_model,Request $request)
	{
		if(Auth::user()->module->whereIn('id',[166,167,168])->count()>0)
		{
			$name       = $request->name;
			$curp       = $request->curp;
			$status     = $request->status;
			$enterprise = $request->enterprise;
			$project    = $request->project;
			$employees 	= App\NominaEmployee::selectRaw('
							real_employees.name as name,
							real_employees.last_name as last_name,
							real_employees.scnd_last_name as scnd_last_name,
							CONCAT(SUBSTRING(curp,9,2),"/",SUBSTRING(curp,7,2),"/",IF( SUBSTRING(curp,5,2) <= DATE_FORMAT(NOW(),"%y"), CONCAT(20,SUBSTRING(curp,5,2)), CONCAT(19,SUBSTRING(curp,5,2)) )) as date_of_birth,
							real_employees.rfc as rfc,
							real_employees.curp as curp,
							real_employees.imss as imss,
							worker_datas.imssDate as admissionDate,
							IF(nomina_employees.idCatPeriodicity = "02","SEM",IF(nomina_employees.idCatPeriodicity = "04","QNAL","")) as periodicity,
							cat_banks.description,
							CONCAT(employee_accounts.clabe," ") as clabe,
							real_employees.email as email,
							real_employees.phone as phone,
							subdepartments.subdepartment as subdepartment,
							worker_datas.position as position,
							real_employees.purpose as purpose,
							nomina_employee_n_fs.amount as netIncome
						')
						->leftJoin('real_employees','nomina_employees.idrealEmployee','real_employees.id')
						->leftJoin('worker_datas','nomina_employees.idworkingData','worker_datas.id')
						->leftJoin(DB::raw('(SELECT working_data_id, GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartment FROM employee_subdepartments JOIN subdepartments ON employee_subdepartments.subdepartment_id = subdepartments.id GROUP BY working_data_id) as subdepartments'),'worker_datas.id','subdepartments.working_data_id')
						->leftJoin('nomina_employee_n_fs','nomina_employees.idnominaEmployee','nomina_employee_n_fs.idnominaEmployee')
						->leftJoin('employee_accounts','nomina_employee_n_fs.idemployeeAccounts','employee_accounts.id')
						->leftJoin('cat_banks','employee_accounts.idCatBank','cat_banks.c_bank')
						->where('nomina_employees.idnomina',$request_model->nominasReal->first()->idnomina)
						->where('nomina_employees.visible',1)
						->orderBy('real_employees.last_name','ASC')
						->orderBy('real_employees.scnd_last_name','ASC')
						->orderBy('real_employees.name','ASC')
						->get();
			if(count($employees)==0 || $employees==null)
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('ED704D')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();

			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-NOM035.xlsx');
			$mainHeaderArr = [
				'N°',
				'NOMBRE (S)',
				'APELLIDO PATERNO',
				'APELLIDO MATERNO',
				'FECHA DE NACIMIENTO (DD/MM/AAAA)',
				'RFC (13 CARACTERES)',
				'CURP',
				'NUM. DE SEG.SOCIAL (11 DIGITOS)',
				'FECHA INGRESO',
				'TIPO DE NOMINA (QNAL/SEM)',
				'BANCO',
				'CLABE INTERBANCARIA A 18 DIGITOS',
				'CORREO',
				'NÚMERO DE CELULAR (10 DIGITOS)',
				'DEPARTAMENTO EMPRESA',
				'PUESTO EN LA EMPRESA',
				'FUNCIONES DEL PUESTO (3 PRINCIPALES FUNCIONES)',
				'IMPORTE NETO A PAGAR POR NOM035'
			];

			$tmpMHArr = [];
			foreach($mainHeaderArr as $k => $mh)
			{
				if($k <= 18)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
			$writer->addRow($rowFromValues);

			$tempCurp	= '';
			$kindRow	= true;
			$count		= 1;
			foreach ($employees as $key => $employee) 
			{
				if($tempCurp != $employee->curp)
				{
					$tempCurp = $employee->curp;
					$kindRow = !$kindRow;
				}
				$array = [];
				$array[] = WriterEntityFactory::createCell($count);
				$array[] = WriterEntityFactory::createCell($employee->name);
				$array[] = WriterEntityFactory::createCell($employee->last_name);
				$array[] = WriterEntityFactory::createCell($employee->scnd_last_name);
				$array[] = WriterEntityFactory::createCell($employee->date_of_birth);
				$array[] = WriterEntityFactory::createCell($employee->rfc);
				$array[] = WriterEntityFactory::createCell($employee->curp);
				$array[] = WriterEntityFactory::createCell($employee->imss);
				$array[] = WriterEntityFactory::createCell($employee->admissionDate);
				$array[] = WriterEntityFactory::createCell($employee->periodicity);
				$array[] = WriterEntityFactory::createCell($employee->description);
				$array[] = WriterEntityFactory::createCell($employee->clabe);
				$array[] = WriterEntityFactory::createCell($employee->email);
				$array[] = WriterEntityFactory::createCell($employee->phone);
				$array[] = WriterEntityFactory::createCell($employee->subdepartment);
				$array[] = WriterEntityFactory::createCell($employee->position);
				$array[] = WriterEntityFactory::createCell($employee->purpose);
				$array[] = WriterEntityFactory::createCell((double) $employee->netIncome, $currencyFormat);

			
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($array,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($array, $alignment);
				}
				$writer->addRow($rowFromValues);
				$count++;
			}
			return $writer->close();
		}
		else
		{
			return redirect('error');
		}
	}
}
