<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployerRegister extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'enterprise_id',
		'employer_register',
		'risk_number',
		'position_risk_id',
	];

	public function Enterprise()
	{
		return $this->belongsTo(Enterprise::class);
	}

	public function positionRisk()
	{
		return $this->hasOne(CatPositionRisk::class,'id','position_risk_id');
	}
}
