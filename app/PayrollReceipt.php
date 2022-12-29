<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PayrollReceipt extends Model
{
	protected $fillable = 
	[
		'idnominaemployeenf',
		'path',
		'signed_at',
	];
	protected $dates = 
	[
		'signed_at',
	];

	public function nominaemployeenf()
	{
		return $this->belongsTo(NominaEmployeeNF::class,'idnominaemployeenf','idnominaemployeenf');
	}
}
