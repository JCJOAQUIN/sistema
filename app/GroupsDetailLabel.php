<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GroupsDetailLabel extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idgroupsDetailLabel';
	protected $fillable   = 
	[
		'idlabels',
		'idgroupsDetail',
	];

	public function label()
	{
		return $this->belongsTo(Label::class,'idlabels','idlabels');
	}
}
