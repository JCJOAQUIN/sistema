<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GroupsDetail extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idgroupsDetail';
	protected $fillable   = 
	[
		'quantity',
		'unity',
		'description',
		'unitPrice',
		'tax',
		'typeTax',
		'subtotal',
		'amount',
		'idgroups',
	];

	public function groups()
	{
		return $this->belongsTo(Groups::class,'idgroups','idgroups');
	}

	public function labels()
	{
		return $this->hasMany(GroupsDetailLabel::class,'idgroupsDetail','idgroupsDetail');
	}

	public function labelsReport()
	{
		return $this->belongsToMany(Label::class,'groups_detail_labels','idgroupsDetail','idlabels','idgroupsDetail','idlabels');
	}

	public function taxes()
	{
		return $this->hasMany(GroupsTaxes::class,'idgroupsDetail','idgroupsDetail');
	}

	public function retentions()
	{
		return $this->hasMany(GroupsRetention::class,'idgroupsDetail','idgroupsDetail');
	}
}
