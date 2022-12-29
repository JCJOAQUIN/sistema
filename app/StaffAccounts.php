<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StaffAccounts extends Model
{
    public $timestamps  = false;
    protected $primaryKey = 'id';
	protected $fillable = 
	[
		'id_employee',
		'alias',
		'clabe',
		'account',
		'cardNumber',
        'branch',
        'id_catbank',
        'recorder',
        'beneficiary',
        'type',
	];

	public function bank()
	{
		return $this->belongsTo(CatBank::class,'id_catbank','c_bank');
	}

}
