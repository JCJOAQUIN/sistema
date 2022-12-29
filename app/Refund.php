<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idRefund';
	protected $fillable   = 
	[
		'idRefund',
		'idFolio',
		'idKind',
		'total',
		'idEmployee',
		'idUsers',
		'reference',
		'idpaymentMethod',
	];

	public function requestModel()
	{
		return $this->belongsTo(RequestModel::class,'idFolio','folio');
	}

	public function refundDetail()
	{
		return $this->hasMany(RefundDetail::class,'idRefund','idRefund');
	}

	public function bankData()
	{
		return $this->belongsTo(Employee::class,'idEmployee','idEmployee');
	}

	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class,'idpaymentMethod','idpaymentMethod');
	}

	public function budget()
	{
		return $this->hasOne(Budget::class,'request_id','idFolio');
	}

	public function getPresupuestoEstatusAttribute()
	{
		$p = $this->budget;
		if($p)
		{
			return $p->status == 1 ? "Aprobada" : "Rechazada";
		}
		else{
			return 'Pendiente';
		}
	}

	public function requisitionRequest()
	{
		return $this->hasOne(RequestModel::class,'folio','idRequisition');
	}
}
