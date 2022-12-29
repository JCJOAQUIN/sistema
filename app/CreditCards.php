<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CreditCards extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idcreditCard';
	protected $fillable   = 
	[
		'idcreditCard',
		'idBanks',
		'alias',
		'name_credit_card',
		'assignment',
		'credit_card',
		'status',
		'type_credit',
		'type_credit_other',
		'cutoff_date',
		'idEnterprise',
		'idAccAcc',
		'payment_date',
		'limit_credit',
		'type_currency',
		'principal_aditional',
		'principal_card_id',
	];

	public function bank()
	{
		return $this->belongsTo(Banks::class,'idBanks','idBanks');
	}

	public function enterprise()
	{
		return $this->hasOne(Enterprise::class,'id','idEnterprise');
	}

	public function accounts()
	{
		return $this->hasOne(Account::class,'idAccAcc','idAccAcc');
	}

	public function user()
	{
		return $this->hasOne(User::class,'id','assignment');
	}
}
