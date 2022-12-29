<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Software extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idsoftware';
	protected $fillable   = 
	[
		'name',
		'kind',
		'required',
		'cost',
	];

	public function software()
	{
		return $this->belongsToMany(Software::class,'computer_software','idComputer','idSoftware');
	}
}