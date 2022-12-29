<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UnsafeAct extends Model
{
    protected $table = 'unsafe_acts';

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
		return $this->hasMany(UnsafeActDocument::class)->where('type',1);
	}

	public function afterDocuments()
	{
		return $this->hasMany(UnsafeActDocument::class)->where('type',2);
	}
}
