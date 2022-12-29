<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobPosition extends Model
{
	protected $fillable	= 
	[
		'name',
		'description',
		'immediate_boss',
		'user_id',
	];

	public function immediateBoss()
	{
		return $this->hasOne(JobPosition::class,'id','immediate_boss');
	}
}
