<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CatCodeWBS extends Model
{
	public $table = 'cat_code_w_bs';
	public $timestamps  = false;
	protected $fillable = 
	[
		'code',
		'code_wbs',
		'project_id',
		'status',
	];

	public function codeEDT()
	{
		return $this->hasMany(CatCodeEDT::class,'codewbs_id','id');
	}

	public function projectData()
	{
		return $this->hasOne(Project::class,'idproyect','project_id');
	}
}
