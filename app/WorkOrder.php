<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'title',
		'elaborate_date',
		'number',
		'date_obra',
		'idFolio',
		'urgent',
		'applicant',
	];

	public function details()
	{
		return $this->hasMany(WorkOrderDetail::class,'idWorkOrder','id');
	}

	public function request()
	{
		return $this->belongsTo(RequestModel::class,'idFolio','idFolio');
	}

	public function relationApplicant()
	{
		return $this->hasOne(CatRequestRequisition::class,'id','applicant');
	}

	public function documents()
	{
		return $this->hasMany(WorkOrderDocuments::class,'idWorkOrder','id');
	}
}
