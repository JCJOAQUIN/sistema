<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
	use SoftDeletes;

	protected $fillable = [
		'text',
		'parent',
		'user_id',
	];

	public function files()
	{
		return $this->hasMany(CompressedFile::class,'folder_id','id');
	}

	public function folders()
	{
		return $this->hasMany(Folder::class,'parent','id');
	}

	public function allFolders()
	{
		return $this->folders()->with('folders');
	}
}
