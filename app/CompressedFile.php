<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompressedFile extends Model
{
	use SoftDeletes;

	protected $fillable = 
	[
		'real_name',
		'file_name',
		'file_extension',
		'user_id',
		'folder_id',
		'file_size',
	];

	public function folder()
	{
		return $this->belongsTo(Folder::class,'folder_id','id');
	}
}
