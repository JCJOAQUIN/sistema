<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OtherIncome extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'title',
		'datetitle',
		'idbanksAccounts',
		'type_income',
		'subtotal',
		'total_iva',
		'total_taxes',
		'total_retentions',
		'total',
		'idFolio',
		'idKind',
		'borrower',
		'type_currency',
		'pay_mode',
		'status_bill',
		'reference',
	];

	public function details()
	{
		return $this->hasMany(OtherIncomeDetail::class,'idOtherIncome','id');
	}

	public function documents()
	{
		return $this->hasMany(OtherIncomeDocuments::class,'idOtherIncome','id');
	}

	public function typeIncome()
	{
		switch ($this->type_income)
		{
			case 1:
				return 'Préstamo de terceros (socios, personales, grupos)';
				break;
			case 2:
				return 'Reembolso/reintegro';
				break;
			case 3:
				return 'Devoluciones';
				break;
			case 4:
				return 'Ganancias por inversión';
				break;
			default:
				return 'Sin tipo';
				break;
		}
	}
}
