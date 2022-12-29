<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CatAccounts extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'name',
		'letter',
	];

	public function fullName()
	{
		return $this->letter.' - '.$this->name;
	}
}
