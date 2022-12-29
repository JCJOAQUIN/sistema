<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
	protected $fillable = 
	[
		'id_enterprise',
		'id_accounting_account',
		'id_bank',
		'currency',
		'clabe',
		'account',
		'kind',
		'description',
	];

	public function bank()
	{
		return $this->belongsTo(Banks::class,'idBanks','id_bank');
	}

	public function enterprise()
	{
		return $this->hasOne(Enterprise::class,'id','id_enterprise');
	}

	public function accounts()
	{
		return $this->belongsTo(Account::class,'idAccAcc','id_accounting_account');
	}
}
