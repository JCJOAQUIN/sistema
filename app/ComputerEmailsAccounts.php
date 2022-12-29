<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ComputerEmailsAccounts extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idcomputerEmailsAccounts';
	protected $fillable   = 
	[
		'email_account',
		'alias_account',
		'idComputer',
	];
}
