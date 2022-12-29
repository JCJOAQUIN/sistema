<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GroupingAccount extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'name',
		'idEnterprise',
	];

	public function hasAccount()
	{
		return $this->hasMany(GroupingHasAccount::class,'idGroupingAccount','id');
	}

	public function enterprise()
	{
		return $this->hasOne(Enterprise::class,'id','idEnterprise');
	}
}
