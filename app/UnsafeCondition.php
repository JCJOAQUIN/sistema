<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UnsafeCondition extends Model
{
	protected $fillable =
	[
		'category_id',
		'subcategory_id',
		'dangerousness',
		'description',
		'action',
		'prevent',
		're',
		'fv',
		'status',
		'responsable',
		'audit_id'
	];

	public function beforeDocuments()
	{
		return $this->hasMany(UnsafeConditionDocument::class)->where('type',1);
	}

	public function afterDocuments()
	{
		return $this->hasMany(UnsafeConditionDocument::class)->where('type',2);
	}
}
