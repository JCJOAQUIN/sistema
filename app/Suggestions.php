<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Suggestions extends Model
{
	const CREATED_AT 	  = 'date';
	protected $primaryKey = 'idSuggestions';
	protected $fillable   = 
	[
		'subject',
		'suggestion',
		'date',
		'idUsers',
	];

	public function user()
	{
		return $this->hasOne(User::class,'id','idUsers');
	}
}
