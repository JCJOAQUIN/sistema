<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IncomeDetail extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idincomeDetail';
	protected $fillable   = 
	[
		'idIncome',
		'quantity',
		'unit',
		'description',
		'unitPrice',
		'tax',
		'discount',
		'typeTax',
		'subtotal',
	];

	public function income()
	{
		return $this->belongsTo(Income::class,'idIncome','idIncome');
	}

	public function labels()
	{
		return $this->hasMany(LabelDetailIncome::class,'idincomeDetail','idincomeDetail');
	}

	public function labelsReport()
	{
		return $this->belongsToMany(Label::class,'label_detail_incomes','idincomeDetail','idlabels','idincomeDetail','idlabels');
	}

	public function taxes()
	{
		return $this->hasMany(TaxesIncome::class,'idincomeDetail','idincomeDetail');
	}

	public function retentions()
	{
		return $this->hasMany(RetentionIncome::class,'idincomeDetail','idincomeDetail');
	}
}
