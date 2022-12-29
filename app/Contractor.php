<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contractor extends Model
{
	protected $table = 'contractors';

	protected $primaryKey = 'id';
	use SoftDeletes;
	
	protected $dates = ['deleted_at'];
	protected $fillable = [
		'id','name','status','project_id'
	];

	public function contract()
	{
		return $this->hasOne(Contract::class,'id','contract_id');
	}
}
