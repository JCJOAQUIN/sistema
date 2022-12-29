<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App;

class ReportFinanceController extends Controller
{
	private $module_id	= 130;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
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
			return abort(404);
		}
	}
}
