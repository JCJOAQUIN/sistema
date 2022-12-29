<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BanksAccounts extends Model
{
	protected $primaryKey = 'idbanksAccounts';
	public $timestamps    = false;
	protected $fillable   = 
	[
		'idBanks',
		'alias',
		'account',
		'branch',
		'reference',
		'clabe',
		'currency',
		'agreement',
		'idEnterprise',
		'idAccAcc'
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
		return $this->belongsTo(Account::class,'idAccAcc','idAccAcc');
	}
}
