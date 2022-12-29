<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExpensesDetail extends Model
{
	protected $primaryKey = 'idExpensesDetail';
	public $timestamps    = false;
	protected $fillable   = 
	[
		'idExpenses',
		'RefundDate',
		'taxPayment',
		'document',
		'concept',
		'amount',
		'tax',
		'sAmount',
		'idAccount',
		'idresourcedetail',
		'idAccountR',
	];

	public function expense()
	{
		return $this->belongsTo(Expenses::class,'idExpenses','idExpenses');
	}

	public function account()
	{
		return $this->hasOne(Account::class,'idAccAcc','idAccount');
	}

	 public function accountR()
	{
		return $this->hasOne(Account::class,'idAccAcc','idAccountR');
	}

	public function labels()
	{
		return $this->hasMany(LabelDetailExpenses::class,'idExpensesDetail','idExpensesDetail');
	}

	public function labelsReport()
	{
		return $this->belongsToMany(Label::class,'label_detail_expenses','idExpensesDetail','idlabels','idExpensesDetail','idlabels');
	}

	public function taxes()
	{
		return $this->hasMany(TaxesExpenses::class,'idExpensesDetail','idExpensesDetail');
	}

    public function documents()
    {
        return $this->hasMany(ExpensesDocuments::class,'idExpensesDetail','idExpensesDetail');
    }
}
