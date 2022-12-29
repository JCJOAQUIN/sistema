<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idLoan';
	protected $fillable   = 
	[
		'kindOfAcount',
		'idUsers',
		'reference',
		'amount',
		'path',
		'transfer',
		'periodicity',
		'beneficiary',
		'idFolio',
		'idKind',
		'idEmployee',
		'idpaymentMethod',
	];

	public function users()
	{
		return $this->hasMany(User::class,'idUsers','id');
	}

	public function bankData()
	{
		return $this->belongsTo(Employee::class,'idEmployee','idEmployee');
	}

	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class,'idpaymentMethod','idpaymentMethod');
	}

	public function setPathAttribute($path)
	{
		if(!empty($path))
		{
			$this->attributes['path'] = 'AdG'.round(microtime(true) * 1000).'_loanDoc.'.$path->getClientOriginalExtension();
			$name = '/docs/loan/AdG'.round(microtime(true) * 1000).'_loanDoc.'.$path->getClientOriginalExtension();
			\Storage::disk('public')->put($name,\File::get($path));
		}
	}
}
