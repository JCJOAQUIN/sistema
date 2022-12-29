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
use Excel;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Illuminate\Support\Str as Str;

class ReporteAdministracionController extends Controller
{
	private $module_id = 96;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			return view('layouts.child_module',
			[
				'id'       => $data['father'],
				'title'    => $data['name'],
				'details'  => $data['details'],
				'child_id' => $this->module_id
			]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function getAccounts(Request $request)
	{
		if ($request->ajax())
		{
			$output 	= "";
			$accounts 	= App\Account::where('idEnterprise',$request->enterpriseid)
							->get();
			if (count($accounts)>0)
			{
				return Response($accounts);
			}
		}
	}
}
