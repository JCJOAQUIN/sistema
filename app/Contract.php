<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CatCodeWBS;

class Contract extends Model
{
	protected $fillable = [
		'number','name','project_id'
	];
	public function pdaData()
	{
		return $this->hasMany(CatContractItem::class,'contract_id','id');
	}
	public function wbs()
	{
		return $this->belongsToMany(CatCodeWBS::class,'w_b_s_contract','contract_id','wbs_id');
	}

	public function project()
	{
		return $this->belongsTo(Project::class,'project_id','idproyect');
	}
}