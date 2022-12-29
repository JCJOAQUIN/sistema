<?php

namespace App\Http\Controllers;
use Auth;
use PDF;
use Illuminate\Http\Request;
use App\Module;
use App\CatCodeWBS;
use App\AuditCategory;
use App\AuditSubcategory;
use App\Audit;
use App\UnsafeAct;
use App\UnsafeActDocument;
use App\UnsafeCondition;
use App\UnsafeConditionDocument;
use App\UnsafePractice;
use App\UnsafePracticeDocument;
use App\AuditHasOtherResponsible;
use App\AuditHasOtherAuditor;
use App\Contractor;
use App\CatAuditor;
use Carbon\Carbon;

class OperationAuditsController extends Controller
{
	private $module_id = 296;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data  = Module::find($this->module_id);
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

	public function create()
	{
		if (Auth::user()->module->where('id',297)->count()>0)
		{
			
			$data 	= Module::find($this->module_id);
			return view('operacion.auditorias.alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id' 	=> $this->module_id,
					'option_id' => 297
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function store(Request $request)
	{		
		if (Auth::user()->module->where('id',297)->count()>0)
		{
			$audit					= new Audit();
			$audit->project_id		= $request->project_id;
			$audit->wbs_id			= $request->code_wbs;

			$contractor = Contractor::where('id',$request->contractor)->first();
			if(!$contractor)
			{
				$contractor			= new Contractor();
				$contractor->name	= $request->contractor;
				$contractor->save();
			} 

			$audit->contractor_id   = $contractor->id;
			$audit->contract		= $request->contract;
			$audit->type_audit		= $request->type_audit;
			$audit->date			= Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d');
			$audit->auditor			= $request->auditor;
			$audit->people_involved	= $request->people_involved;

			$auditor = CatAuditor::where('id',$request->cat_auditor_id)->first();
			if(!$auditor)
			{
				$auditor		= new CatAuditor();
				$auditor->name	= $request->cat_auditor_id;
				$auditor->save();
			} 

			$audit->cat_auditor_id	= $auditor->id;
			$audit->pti_responsible	= $request->pti_responsible;
			$audit->observations	= $request->observations;
			$audit->user_id			= Auth::user()->id;
			$audit->save();

			if (isset($request->other_responsible) && count($request->other_responsible)>0) 
			{
				for ($i=0; $i < count($request->other_responsible); $i++) 
				{ 
					if ($request->other_responsible[$i] != "") 
					{
						if ($request->other_responsible_id[$i] != "x")
						{
							$other_responsible			= AuditHasOtherResponsible::find($request->other_responsible_id[$i]);
						}
						else
						{
							$other_responsible			= new AuditHasOtherResponsible();
						}
						$other_responsible->name		= $request->other_responsible[$i];
						$other_responsible->audit_id	= $audit->id;
						$other_responsible->save();
					}
				}
			}

			if (isset($request->other_auditors_exists) && count($request->other_auditors_exists)>0) 
			{
				for ($i=0; $i < count($request->other_auditors_exists); $i++) 
				{ 
					if ($request->other_auditors_exists[$i] != "") 
					{
						$other_auditor				= new AuditHasOtherAuditor();

						$auditor = CatAuditor::where('name',$request->other_auditors_exists[$i])->first();
						if(!$auditor)
						{
							$auditor		= new CatAuditor();
							$auditor->name	= $request->other_auditors_exists[$i];
							$auditor->save();
						} 

						$other_auditor->name		= $auditor->name;
						$other_auditor->audit_id	= $audit->id;
						$other_auditor->type 		= 1;
						$other_auditor->save();
					}
				}
			}

			if (isset($request->other_auditors_new) && count($request->other_auditors_new)>0) 
			{
				for ($i=0; $i < count($request->other_auditors_new); $i++) 
				{ 
					if ($request->other_auditors_new[$i] != "") 
					{
						if ($request->other_auditors_new_id[$i] != "x") 
						{
							$other_auditor			= AuditHasOtherAuditor::find($request->other_auditors_new_id[$i]);
						}
						else
						{
							$other_auditor			= new AuditHasOtherAuditor();
						}
						$other_auditor->name		= $request->other_auditors_new[$i];
						$other_auditor->audit_id	= $audit->id;
						$other_auditor->type 		= 2;
						$other_auditor->save();
					}
				}
			}

			if(isset($request->ua_category_id) && count($request->ua_category_id)>0)
			{
				for($i=0; $i<count($request->ua_category_id); $i++)
				{
					if($request->ua_category_id[$i] != "")
					{
						$da = $i+1; //var x
    	 				$ua_before_real_path 	= 'ua_before_real_path_'.$da;
    	 				$ua_after_real_path 	= 'ua_after_real_path_'.$da;

    	 				$new_ua 				= new UnsafeAct();
						$new_ua->category_id	= $request->ua_category_id[$i];
						$new_ua->subcategory_id	= $request->ua_subcategory_id[$i];
						$new_ua->dangerousness	= $request->ua_dangerousness[$i];
						$new_ua->description	= $request->ua_description[$i];
						$new_ua->action			= $request->ua_action[$i];
						$new_ua->prevent		= $request->ua_prevent[$i];
						$new_ua->re				= $request->ua_re[$i];
						$new_ua->fv				= Carbon::createFromFormat('d-m-Y',$request->ua_fv[$i])->format('Y-m-d');
						$new_ua->status			= $request->ua_status[$i];
						$new_ua->responsable	= $request->ua_responsable[$i];
						$new_ua->audit_id		= $audit->id;
						$new_ua->save();

						if (isset($request->$ua_before_real_path) && count($request->$ua_before_real_path)>0) 
						{
							for ($b=0; $b < count($request->$ua_before_real_path); $b++) 
							{ 
								if($request->$ua_before_real_path[$b] != "")
								{
									$new_doc_before							= new UnsafeActDocument();
									$new_doc_before->path					= $request->$ua_before_real_path[$b];
									$new_doc_before->unsafe_act_id			= $new_ua->id;
									$new_doc_before->type					= 1;
									$new_doc_before->save();
								}
							}
						}
						if (isset($request->$ua_after_real_path) && count($request->$ua_after_real_path)>0) 
						{
							for ($a=0; $a < count($request->$ua_after_real_path); $a++) 
							{ 
								if($request->$ua_after_real_path[$a] != "")
								{
									$new_doc_after						= new UnsafeActDocument();
									$new_doc_after->path				= $request->$ua_after_real_path[$a];
									$new_doc_after->unsafe_act_id		= $new_ua->id;
									$new_doc_after->type				= 2;
									$new_doc_after->save();
								}
							}
						}
					}
				}
			}

			if (isset($request->up_category_id) && count($request->up_category_id)>0) 
    	 	{
    	 		for ($i=0; $i < count($request->up_category_id); $i++) 
    	 		{ 
    	 			if ($request->up_category_id[$i] != "") 
    	 			{
    	 				$ms = $i+1; //var x
    	 				$up_before_real_path 	= 'up_before_real_path_'.$ms;
    	 				$up_after_real_path 	= 'up_after_real_path_'.$ms;

    	 				$new_up 				= new UnsafePractice();
						$new_up->category_id	= $request->up_category_id[$i];
						$new_up->subcategory_id	= $request->up_subcategory_id[$i];
						$new_up->dangerousness	= $request->up_dangerousness[$i];
						$new_up->description	= $request->up_description[$i];
						$new_up->action			= $request->up_action[$i];
						$new_up->prevent		= $request->up_prevent[$i];
						$new_up->re				= $request->up_re[$i];
						$new_up->fv				= Carbon::createFromFormat('d-m-Y',$request->up_fv[$i])->format('Y-m-d');
						$new_up->status			= $request->up_status[$i];
						$new_up->responsable	= $request->up_responsable[$i];
						$new_up->audit_id		= $audit->id;
						$new_up->save();

						if (isset($request->$up_before_real_path) && count($request->$up_before_real_path)>0) 
						{
							for ($b=0; $b < count($request->$up_before_real_path); $b++) 
							{ 
								if ($request->$up_before_real_path[$b] != "") 
								{
									$new_doc_before							= new UnsafePracticeDocument();
									$new_doc_before->path					= $request->$up_before_real_path[$b];
									$new_doc_before->unsafe_practice_id	= $new_up->id;
									$new_doc_before->type					= 1;
									$new_doc_before->save();
								}
							}
						}

						if (isset($request->$up_after_real_path) && count($request->$up_after_real_path)>0) 
						{
							for ($a=0; $a < count($request->$up_after_real_path); $a++) 
							{ 
								if ($request->$up_after_real_path[$a] != "") 
								{
									$new_doc_after						= new UnsafePracticeDocument();
									$new_doc_after->path				= $request->$up_after_real_path[$a];
									$new_doc_after->unsafe_practice_id	= $new_up->id;
									$new_doc_after->type				= 2;
									$new_doc_after->save();
								}
							}
						}

					}
				}
			}

    	 	if (isset($request->uc_category_id) && count($request->uc_category_id)>0) 
    	 	{
    	 		for ($i=0; $i < count($request->uc_category_id); $i++) 
    	 		{ 
    	 			if ($request->uc_category_id[$i] != "") 
    	 			{
    	 				$ms = $i+1; //var x
    	 				$uc_before_real_path 	= 'uc_before_real_path_'.$ms;
    	 				$uc_after_real_path 	= 'uc_after_real_path_'.$ms;

    	 				$new_uc 				= new UnsafeCondition();
						$new_uc->category_id	= $request->uc_category_id[$i];
						$new_uc->subcategory_id	= $request->uc_subcategory_id[$i];
						$new_uc->dangerousness	= $request->uc_dangerousness[$i];
						$new_uc->description	= $request->uc_description[$i];
						$new_uc->action			= $request->uc_action[$i];
						$new_uc->prevent		= $request->uc_prevent[$i];
						$new_uc->re				= $request->uc_re[$i];
						$new_uc->fv				= Carbon::createFromFormat('d-m-Y',$request->uc_fv[$i])->format('Y-m-d');
						$new_uc->status			= $request->uc_status[$i];
						$new_uc->responsable	= $request->uc_responsable[$i];
						$new_uc->audit_id		= $audit->id;
						$new_uc->save();

						if (isset($request->$uc_before_real_path) && count($request->$uc_before_real_path)>0) 
						{
							for ($b=0; $b < count($request->$uc_before_real_path); $b++) 
							{ 
								if ($request->$uc_before_real_path[$b] != "") 
								{
									$new_doc_before							= new UnsafeConditionDocument();
									$new_doc_before->path					= $request->$uc_before_real_path[$b];
									$new_doc_before->unsafe_condition_id	= $new_uc->id;
									$new_doc_before->type					= 1;
									$new_doc_before->save();
								}
							}
						}

						if (isset($request->$uc_after_real_path) && count($request->$uc_after_real_path)>0) 
						{
							for ($a=0; $a < count($request->$uc_after_real_path); $a++) 
							{ 
								if ($request->$uc_after_real_path[$a] != "") 
								{
									$new_doc_after						= new UnsafeConditionDocument();
									$new_doc_after->path				= $request->$uc_after_real_path[$a];
									$new_doc_after->unsafe_condition_id	= $new_uc->id;
									$new_doc_after->type				= 2;
									$new_doc_after->save();
								}
							}
						}

					}
				}
			}

			// IAI, IAS, Severity_factor
			$auditData		=	Audit::find($audit->id);
			
			$OneThird		= $auditData->countDangerousnessOneThird();
			$One			= $auditData->countDangerousnessOne();
			$Three			= $auditData->countDangerousnessThree();
			$total_persons	= $auditData->people_involved;

			if ($total_persons > 0) 
			{
				$iai = round(((($OneThird * (1/3)) + ($One * 1) + ($Three*3))/$total_persons)*100,2);
				$ias = round(100 - $iai,2);
			}
			else
			{
				$iai = 0;
                $ias = 0;
			}
			
			$auditData->iai				=	$iai;
			$auditData->severity_factor	=	($OneThird+$One+$Three);
			$auditData->ias				=	$ias;
			$auditData->save();

			$alert = "swal('','Auditoría creada exitosamente.','success')";
			return redirect()->route('audits.index')->with('alert',$alert);
		}
		else
		{
			return redirect('error');
		}
	}

	public function update(Audit $audit,Request $request)
	{
		if (Auth::user()->module->where('id',297)->count()>0)
		{
		$audit->project_id		= $request->project_id;
		$audit->wbs_id			= $request->code_wbs;

		$contractor = Contractor::where('id',$request->contractor)->first();
		if(!$contractor)
		{
			$contractor			= new Contractor();
			$contractor->name	= $request->contractor;
			$contractor->save();
		} 

		$audit->contractor_id   = $contractor->id;
		$audit->contract		= $request->contract;
		$audit->type_audit		= $request->type_audit;
		$audit->date			= Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d');;
		$audit->auditor			= $request->auditor;
		$audit->user_id			= Auth::user()->id;
		$audit->people_involved	= $request->people_involved;

		$auditor = CatAuditor::where('id',$request->cat_auditor_id)->first();
		if(!$auditor)
		{
			$auditor		= new CatAuditor();
			$auditor->name	= $request->cat_auditor_id;
			$auditor->save();
		} 

		$audit->cat_auditor_id	= $auditor->id;
		$audit->pti_responsible	= $request->pti_responsible;
		$audit->observations	= $request->observations;
		$audit->user_id			= Auth::user()->id;
		$audit->save();

		if (isset($request->delete_other_responsible) && count($request->delete_other_responsible)>0)
		{
			AuditHasOtherResponsible::whereIn('id',$request->delete_other_responsible)->delete();
		}
		
		if (isset($request->delete_other_auditor) && count($request->delete_other_auditor)>0)
		{
			AuditHasOtherAuditor::whereIn('id',$request->delete_other_auditor)->delete();
		}

		if (isset($request->other_responsible) && count($request->other_responsible)>0) 
		{
			for ($i=0; $i < count($request->other_responsible); $i++) 
			{ 
				if ($request->other_responsible[$i] != "") 
				{
					if ($request->other_responsible_id[$i] != "x")
					{
						$other_responsible			= AuditHasOtherResponsible::find($request->other_responsible_id[$i]);
					}
					else
					{
						$other_responsible			= new AuditHasOtherResponsible();
					}
					$other_responsible->name		= $request->other_responsible[$i];
					$other_responsible->audit_id	= $audit->id;
					$other_responsible->save();
				}
			}
		}

		if (AuditHasOtherAuditor::where('audit_id',$audit->id)->where('type',1)->count()>0)
		{
			AuditHasOtherAuditor::where('audit_id',$audit->id)->where('type',1)->delete();
		}
		if (isset($request->other_auditors_exists) && count($request->other_auditors_exists)>0) 
		{
			for ($i=0; $i < count($request->other_auditors_exists); $i++) 
			{ 
				if ($request->other_auditors_exists[$i] != "") 
				{
					$other_auditor				= new AuditHasOtherAuditor();

					$auditor = CatAuditor::where('name',$request->other_auditors_exists[$i])->first();
					if(!$auditor)
					{
						$auditor		= new CatAuditor();
						$auditor->name	= $request->other_auditors_exists[$i];
						$auditor->save();
					} 

					$other_auditor->name		= $auditor->name;
					$other_auditor->audit_id	= $audit->id;
					$other_auditor->type 		= 1;
					$other_auditor->save();
				}
			}
		}

		if (isset($request->other_auditors_new) && count($request->other_auditors_new)>0) 
		{
			for ($i=0; $i < count($request->other_auditors_new); $i++) 
			{ 
				if ($request->other_auditors_new[$i] != "") 
				{
					if ($request->other_auditors_new_id[$i] != "x") 
					{
						$other_auditor			= AuditHasOtherAuditor::find($request->other_auditors_new_id[$i]);
					}
					else
					{
						$other_auditor			= new AuditHasOtherAuditor();
					}
					$other_auditor->name		= $request->other_auditors_new[$i];
					$other_auditor->audit_id	= $audit->id;
					$other_auditor->type 		= 2;
					$other_auditor->save();
				}
			}
		}

		if(isset($request->delete_ua) && count($request->delete_ua)>0)
		{
			UnsafeActDocument::whereIn('unsafe_act_id',$request->delete_ua)->delete();
			UnsafeAct::whereIn('id',$request->delete_ua)->delete();
		}

		if(isset($request->ua_category_id) && count($request->ua_category_id)>0)
		{
			for ($i=0; $i < count($request->ua_category_id); $i++) 
			{ 
				if ($request->ua_category_id[$i] != "") 
				{
					$da = $i+1; //var x
					$ua_before_real_path 	= 'ua_before_real_path_'.$da;
					$ua_after_real_path 	= 'ua_after_real_path_'.$da;

					if ($request->ua_id[$i] == "x") 
					{
						$new_ua 			= new UnsafeAct();
					}
					else
					{
						$new_ua 			= UnsafeAct::find($request->ua_id[$i]);
					}
					$new_ua->category_id	= $request->ua_category_id[$i];
					$new_ua->subcategory_id	= $request->ua_subcategory_id[$i];
					$new_ua->dangerousness	= $request->ua_dangerousness[$i];
					$new_ua->description	= $request->ua_description[$i];
					$new_ua->action			= $request->ua_action[$i];
					$new_ua->prevent		= $request->ua_prevent[$i];
					$new_ua->re				= $request->ua_re[$i];
					$new_ua->fv				= Carbon::createFromFormat('d-m-Y',$request->ua_fv[$i])->format('Y-m-d');
					$new_ua->status			= $request->ua_status[$i];
					$new_ua->responsable	= $request->ua_responsable[$i];
					$new_ua->audit_id		= $audit->id;
					$new_ua->save();

					if (isset($request->$ua_before_real_path) && count($request->$ua_before_real_path)>0) 
					{
						for ($b=0; $b < count($request->$ua_before_real_path); $b++) 
						{ 
							if ($request->$ua_before_real_path[$b] != "") 
							{
								$new_doc_before							= new UnsafeActDocument();
								$new_doc_before->path					= $request->$ua_before_real_path[$b];
								$new_doc_before->unsafe_act_id			= $new_ua->id;
								$new_doc_before->type					= 1;
								$new_doc_before->save();
							}
						}
					}

					if (isset($request->$ua_after_real_path) && count($request->$ua_after_real_path)>0) 
					{
						for ($a=0; $a < count($request->$ua_after_real_path); $a++) 
						{ 
							if ($request->$ua_after_real_path[$a] != "") 
							{
								$new_doc_after						= new UnsafeActDocument();
								$new_doc_after->path				= $request->$ua_after_real_path[$a];
								$new_doc_after->unsafe_act_id		= $new_ua->id;
								$new_doc_after->type				= 2;
								$new_doc_after->save();
							}
						}
					}

				}
			}
		}

		if (isset($request->delete_up) && count($request->delete_up)>0) 
		{
				UnsafePracticeDocument::whereIn('unsafe_practice_id',$request->delete_up)->delete();
				UnsafePractice::whereIn('id',$request->delete_up)->delete();
		}
		if (isset($request->up_category_id) && count($request->up_category_id)>0) 
		{
			for ($i=0; $i < count($request->up_category_id); $i++) 
			{ 
				if ($request->up_category_id[$i] != "") 
				{
					$ms = $i+1; 
					$up_before_real_path 	= 'up_before_real_path_'.$ms;
					$up_after_real_path 	= 'up_after_real_path_'.$ms;

					if ($request->up_id[$i] == "x") 
					{
						$new_up 			= new UnsafePractice();
					}
					else
					{
						$new_up 			= UnsafePractice::find($request->up_id[$i]);
					}
					$new_up->category_id	= $request->up_category_id[$i];
					$new_up->subcategory_id	= $request->up_subcategory_id[$i];
					$new_up->dangerousness	= $request->up_dangerousness[$i];
					$new_up->description	= $request->up_description[$i];
					$new_up->action			= $request->up_action[$i];
					$new_up->prevent		= $request->up_prevent[$i];
					$new_up->re				= $request->up_re[$i];
					$new_up->fv				= Carbon::createFromFormat('d-m-Y',$request->up_fv[$i])->format('Y-m-d');
					$new_up->status			= $request->up_status[$i];
					$new_up->responsable	= $request->up_responsable[$i];
					$new_up->audit_id		= $audit->id;
					$new_up->save();

					if (isset($request->$up_before_real_path) && count($request->$up_before_real_path)>0) 
					{
						for ($b=0; $b < count($request->$up_before_real_path); $b++) 
						{ 
							if ($request->$up_before_real_path[$b] != "") 
							{
								$new_doc_before							= new UnsafePracticeDocument();
								$new_doc_before->path					= $request->$up_before_real_path[$b];
								$new_doc_before->unsafe_practice_id		= $new_up->id;
								$new_doc_before->type					= 1;
								$new_doc_before->save();
							}
						}
					}

					if (isset($request->$up_after_real_path) && count($request->$up_after_real_path)>0) 
					{
						for ($a=0; $a < count($request->$up_after_real_path); $a++) 
						{ 
							if ($request->$up_after_real_path[$a] != "") 
							{
								$new_doc_after						= new UnsafePracticeDocument();
								$new_doc_after->path				= $request->$up_after_real_path[$a];
								$new_doc_after->unsafe_practice_id	= $new_up->id;
								$new_doc_after->type				= 2;
								$new_doc_after->save();
							}
						}
					}

				}
			}
		}

		if (isset($request->delete_uc) && count($request->delete_uc)>0) 
		{
				UnsafeConditionDocument::whereIn('unsafe_condition_id',$request->delete_uc)->delete();
				UnsafeCondition::whereIn('id',$request->delete_uc)->delete();
		}
		if (isset($request->uc_category_id) && count($request->uc_category_id)>0) 
		{
			for ($i=0; $i < count($request->uc_category_id); $i++) 
			{ 
				if ($request->uc_category_id[$i] != "") 
				{
					$ms = $i+1; //var x
					$uc_before_real_path 	= 'uc_before_real_path_'.$ms;
					$uc_after_real_path 	= 'uc_after_real_path_'.$ms;

					if ($request->uc_id[$i] == "x") 
					{
						$new_uc 			= new UnsafeCondition();
					}
					else
					{
						$new_uc 			= UnsafeCondition::find($request->uc_id[$i]);
					}
					$new_uc->category_id	= $request->uc_category_id[$i];
					$new_uc->subcategory_id	= $request->uc_subcategory_id[$i];
					$new_uc->dangerousness	= $request->uc_dangerousness[$i];
					$new_uc->description	= $request->uc_description[$i];
					$new_uc->action			= $request->uc_action[$i];
					$new_uc->prevent		= $request->uc_prevent[$i];
					$new_uc->re				= $request->uc_re[$i];
					$new_uc->fv				= Carbon::createFromFormat('d-m-Y',$request->uc_fv[$i])->format('Y-m-d');
					$new_uc->status			= $request->uc_status[$i];
					$new_uc->responsable	= $request->uc_responsable[$i];
					$new_uc->audit_id		= $audit->id;
					$new_uc->save();

					if (isset($request->$uc_before_real_path) && count($request->$uc_before_real_path)>0) 
					{
						for ($b=0; $b < count($request->$uc_before_real_path); $b++) 
						{ 
							if ($request->$uc_before_real_path[$b] != "") 
							{
								$new_doc_before							= new UnsafeConditionDocument();
								$new_doc_before->path					= $request->$uc_before_real_path[$b];
								$new_doc_before->unsafe_condition_id	= $new_uc->id;
								$new_doc_before->type					= 1;
								$new_doc_before->save();
							}
						}
					}

					if (isset($request->$uc_after_real_path) && count($request->$uc_after_real_path)>0) 
					{
						for ($a=0; $a < count($request->$uc_after_real_path); $a++) 
						{ 
							if ($request->$uc_after_real_path[$a] != "") 
							{
								$new_doc_after						= new UnsafeConditionDocument();
								$new_doc_after->path				= $request->$uc_after_real_path[$a];
								$new_doc_after->unsafe_condition_id	= $new_uc->id;
								$new_doc_after->type				= 2;
								$new_doc_after->save();
							}
						}
					}

				}
			}
		}

		// IAI, IAS, Severity_factor

		$auditData		=	Audit::find($audit->id);
		
		$OneThird		= $auditData->countDangerousnessOneThird();
		$One			= $auditData->countDangerousnessOne();
		$Three			= $auditData->countDangerousnessThree();
		$total_persons	= $auditData->people_involved;

		if ($total_persons > 0) 
		{
			$iai = round(((($OneThird * (1/3)) + ($One * 1) + ($Three*3))/$total_persons)*100,2);
			$ias = round(100 - $iai,2);
		}
		else
		{
			$iai = 0;
			$ias = 0;
		}
		
		$auditData->iai				=	$iai;
		$auditData->severity_factor	=	($OneThird+$One+$Three);
		$auditData->ias				=	$ias;
		$auditData->save();

		$alert = "swal('','Auditoría actualizada exitosamente.','success')";
		return redirect()->route('audits.edit',$audit->id)->with('alert',$alert);
		}
		else
		{
		return redirect('error');
		}
	}

	public function follow(Request $request)
	{
		if (Auth::user()->module->where('id',298)->count() > 0) 
		{
			$data = Module::find($this->module_id);

			$mindate = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : '';
			$maxdate = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : '';

			if (($mindate == "" && $maxdate != "") || ($mindate != "" && $maxdate == "")) 
			{
				$alert = "swal('','Debe ingresar un rango de fechas','error');";
				return back()->with('alert',$alert);
			}

			$audits = Audit::whereIn('project_id',Auth::user()->inChargeProject(298)->pluck('project_id'))
					->where(function($query) use($request, $mindate, $maxdate)
					{
						if($request->project_id != "")
						{
							$query->where('project_id',$request->project_id);
						}

						if($request->wbs_id != "")
						{
							$query->whereIn('wbs_id',$request->wbs_id);
						}

						if($request->contractor_id != "")
						{
							$query->whereIn('contractor_id',$request->contractor_id);
						}

						if($request->type_audit != "")
						{
							$query->whereIn('type_audit',$request->type_audit);
						}

						if($request->folio != "")
						{
							$query->where('id',$request->folio);
						}

						if($request->auditor != "")
						{
							$query->where('auditor','like','%'.$request->auditor.'%');
						}

						if($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('date',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
						}
					})
					->orderBy('id','DESC')
					->paginate(15);

			return view('operacion.auditorias.seguimiento',
			[
				'id'			=> $data['father'],
				'title'			=> $data['name'],
				'details'		=> $data['details'],
				'child_id'		=> $this->module_id,
				'option_id'		=> 298,
				'audits'		=> $audits,
				'project_id'	=> $request->project_id,
				'wbs_id'		=> $request->wbs_id,
				'contractor_id'	=> $request->contractor_id,
				'type_audit'	=> $request->type_audit,
				'folio'			=> $request->folio,
				'auditor'		=> $request->auditor,
				'min_date'		=> $request->mindate,
				'max_date'		=> $request->maxdate,
			]);
		}
	}

	public function edit(Audit $audit)
	{
		if(Auth::user()->module->where('id',298)->count()>0)
		{
			$data	= Module::find($this->module_id);
			return view('operacion.auditorias.alta',
			[
				'id'			=> $data['father'],
				'title'			=> $data['name'],
				'details'		=> $data['details'],
				'child_id'		=> $this->module_id,
				'option_id'		=> 298,
				'audit'			=> $audit
			]);
		}
		else
		{
			return redirect('error');
		}
	}

	public function exportDosBocas(Audit $audit)
	{
		if(Auth::user()->module->where('id',298)->count()>0)
		{
			$data	= Module::find($this->module_id);
			$pdf	= PDF::loadView('operacion.auditorias.documentos.documento_dos_bocas',['audit'=>$audit]);
			return $pdf->download('auditoria_'.$audit->id.'.pdf');
		}
		else
		{
			return redirect('/');
		}
	}

	public function getSubCat(Request $request)
	{
		if ($request->ajax()) 
		{
			$subCat = AuditSubcategory::where('audit_category_id',$request->id_category)->orderBy('id','asc')->get();
			return Response($subCat);
		}
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
					\Storage::disk('public')->delete('/docs/audits/'.$request->realPath[$i]);
				}
				
			}
			if($request->file('path'))
			{
				$extention				= strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_auditsDoc.';
				$name					= $nameWithoutExtention.$extention;
				$destinity				= '/docs/audits/'.$name;
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
			}
			return Response($response);
		}
	}
	public function analytics(Request $request)
	{
		//return dd($request->maxdate);
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$mindate = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : '';
			$maxdate = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : '';

			if($mindate != "" && $maxdate != "")
			{
				$data  = Module::find($this->module_id);
				
				$audits = Audit::whereIn('project_id',Auth::user()->inChargeProject(298)->pluck('project_id'))
					->where(function($query) use($request,$mindate,$maxdate)
					{
						$query->whereBetween('date',[$mindate, $maxdate]);
						if($request->project_id != "")
						{
							$query->where('project_id', $request->project_id);
						}
					})
					->orderBy('date', 'asc')
					->get();	
					$auditsWBS			= $audits->groupBy('wbs_id', true);
					$sectionAudit = ["0" => "unsafe_acts", "1" => "unsafe_practices", "2" => "unsafe_conditions"];
					
					$k = 0;
					$mainArray = [];

				foreach($sectionAudit as $sectAudit)
				{
					$categories =  AuditCategory::get();
					$query	= "";
					$i = 0;
					foreach($categories as $category)
					{
						$query .= 'SUM(IF('.$sectAudit.'.category_id = '.$category["id"].',1,0)) AS \''.$category["id"].'\', ';
						$i++;
					}

					$query .= "concat('') as tmp ";
					
					$categories = Audit::selectRaw(
							$query
						)
						->whereIn('project_id',Auth::user()->inChargeProject(298)->pluck('project_id'))
						->where(function($query) use($request,$mindate,$maxdate)
						{
							$query->whereBetween('date',[$mindate, $maxdate]);
							if($request->project_id != "")
							{
								$query->where('project_id', $request->project_id);
							}
						})
						->leftJoin($sectionAudit[$k],'audits.id','=', $sectionAudit[$k].'.audit_id')
						->get();
						$mainArray[] = $categories;
					$k++;
				}
				
				$category_values = [];
				$categories = json_decode(json_encode($mainArray), true);

				foreach($categories as $t_category)
				{
					$i = 0;
					foreach($t_category as $real_category)
					{
						unset($real_category["tmp"]);
						$category_values[] = $real_category;
						$i++;
					}
				}
				$values = [];
				$i = 0;
				foreach($category_values as $category_array)
				{
					for($j=0; $j<5; $j++)
					{
						$values[$j][] = $category_array[$j];
					}
					$i++;
				}
				$last_values = [];
				foreach($values as $last_category)
				{
					$last_values[] = array_sum($last_category);
				}
				
				
				$subCategories = AuditSubcategory::get();
				$subCategoriesArray = $subCategories->groupBy('audit_category_id', true);
				$query = "";

				$mainArray 	= [];
				$i = 0;
				foreach($sectionAudit as $sectAudit)
				{
					$subCategories =  AuditSubcategory::get();
					$query	= "";
					
					foreach($subCategories as $subcategory)
					{
						$query .= 'SUM(IF('.$sectAudit.'.subcategory_id = '.$subcategory["id"].',1,0)) AS \''.$i.'\', ';
						$i++;
					}

					$query .= "concat('') as tmp ";
					
					$categories = Audit::selectRaw(
							$query
						)
						->whereIn('project_id',Auth::user()->inChargeProject(298)->pluck('project_id'))
						->where(function($query) use($request,$mindate,$maxdate)
						{
							$query->whereBetween('date',[$mindate, $maxdate]);
							if($request->project_id != "")
							{
								$query->where('project_id', $request->project_id);
							}
						})
						->leftJoin($sectAudit,'audits.id','=', $sectAudit.'.audit_id')
						->get();
						
						$mainArray[] = $categories;
				}
				$categories = json_decode(json_encode($mainArray), true);
				$actsSubcategories = $categories[0][0];
				$practicesSubcategories = $categories[1][0];
				$conditionsSubcategories = $categories[2][0];

				$total = [];
				for($i = 0; $i < 38; $i++) {
					$total[] = $actsSubcategories[$i] + $practicesSubcategories[$i] + $conditionsSubcategories[$i];
				}
				$last_A = array_slice($total, 0, 6);
				$last_B = array_slice($total, 6, 7);
				$last_C = array_slice($total, 13, 12);
				$last_D = array_slice($total, 25, 4);
				$last_E = array_slice($total, 29, 9);

				$realArraySubcategoryValues = [$last_A, $last_B, $last_C, $last_D, $last_E];
				
				$auditsWBS			= $audits->groupBy('wbs_id', true);
				$auditsContractor	= $audits->groupBy('contractor_id', true);
				

				$dataArrayAudits = [];
				foreach($auditsWBS as $auditWBS)
				{
					$AuditsWBS = $severityFactor= 0; 

					foreach($auditWBS as $detailsAuditWBS)
					{
					   $AuditsWBS  		= ($AuditsWBS+$detailsAuditWBS['ias']);
					   $dataWBS  		= CatCodeWBS::find($detailsAuditWBS->wbs_id);
					   	if($dataWBS != "")
						{
					   		$wbsDescription 	= $dataWBS->code_wbs;
						}
						else
						{
							$wbsDescription 	= "Sin WBS";
						}
					   	$severityFactor 	= ($severityFactor + $detailsAuditWBS['severity_factor']);
					}
					
					$dataArrayAudits[] =  ['AuditsWBS'=>$AuditsWBS/count($auditWBS),"wbs_description" => $wbsDescription, "severity_factor" => $severityFactor, "quantity_wbs" => count($auditWBS)];
					
				}
				
				$subcategories = AuditSubcategory::get();
				$subcategoriesArray = $subcategories->groupBy('audit_category_id', true);

				return view('operacion.auditorias.analitica',
					[
						'id' 							=> $data['father'],
						'title'							=> $data['name'],
						'details'						=> $data['details'],
						'option_id' 					=> 312,
						'child_id'						=> $this->module_id,
						'mindate'						=> $request->mindate,
						'maxdate'						=> $request->maxdate,
						"project_id"					=> [$request->project_id],
						'audits'						=> $audits,
						'auditsWBS'						=> $auditsWBS,
						'dataArrayAudits'				=> $dataArrayAudits,
						'auditsContractor'				=> $auditsContractor,
						'categories'					=> $last_values,
						'categoriesReal'				=> AuditCategory::get(),
						'subcategoriesArray' 			=> $subcategoriesArray,
						'real_array_subcategory_values' => $realArraySubcategoryValues
					]
				);
			}
			else
			{
				$data  = Module::find($this->module_id);

				return view('operacion.auditorias.analitica',[
					'id' 		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id' => 312]
				);
			}
		}
		else
		{
			return redirect('/');
		}
		
	}

	public function exportTula( Request $request, Audit $audit)
	{
		if(Auth::user()->module->where('id',298)->count()>0)
		{
			$data	= Module::find($this->module_id);
			$subcategories = AuditSubcategory::get();
			$subcategories =  $subcategories->groupBy('audit_category_id');		
			
			$pdf	= PDF::loadView('operacion.auditorias.documentos.documento_tula',['audit'=>$audit, 'subcategories' => $subcategories]);
			return $pdf->download('auditoria_'.$audit->id.'.pdf');
		}
		else
		{
			return redirect('/');
		}
	}
	public function exportPIM( Request $request, Audit $audit)
	{
		if(Auth::user()->module->where('id',298)->count()>0)
		{
			$data	= Module::find($this->module_id);
			$subcategories = AuditSubcategory::get();
			$subcategories =  $subcategories->groupBy('audit_category_id');		
			
			$pdf	= PDF::loadView('operacion.auditorias.documentos.documento_pim',['audit'=>$audit, 'subcategories' => $subcategories]);
			return $pdf->download('auditoria_'.$audit->id.'.pdf');
		}
		else
		{
			return redirect('/');
		}
	}
}
