<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcessFolder extends Model
{
	protected $fillable = [
		'text',
		'parent',
		'user_id',
	];

	public function files()
	{
		return $this->hasMany(ProcessFile::class,'folder_id','id');
	}

	public function folders()
	{
		return $this->hasMany(ProcessFolder::class,'parent','id');
	}

	public function allFolders()
	{
		return $this->folders()->with('folders');
	}
}
