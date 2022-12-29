<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdministrativeBudgetDetail extends Model
{
	protected $fillable =
	[
		'account',
		'account_id',
		'amount',
		'amount_spent',
		'alert_percent',
		'status',
		'idAdministrativeBudget',
	];

	public function account()
	{
		return $this->hasOne(Account::class,'idAccAcc','account_id');
	}
}
