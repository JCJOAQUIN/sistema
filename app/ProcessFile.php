<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcessFile extends Model
{
	protected $fillable = 
	[
		'real_name',
		'user_id',
		'folder_id',
	];

	public function folder()
	{
		return $this->belongsTo(ProcessFolder::class,'folder_id','id');
	}
}
