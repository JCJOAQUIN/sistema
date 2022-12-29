<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PartialPayment extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'payment',
		'tipe',
		'date_requested',
		'date_delivery',
		'purchase_id',
		'payment_id',
	];

	public function purchase()
	{
		return $this->belongsTo(Purchase::class,'purchase_id','idPurchase');
	}

	public function payment()
	{
		return $this->hasOne(Payment::class,'partial_id','id');
	}

	public function documentsPartials()
	{
		return $this->hasMany(DocumentsPartials::class,'partial_id','id');
	}

	public function paymentPartial()
	{
		return $this->belongsTo(Payment::class,'payment_id','idpayment');
	}

	public function setPathAttribute($path)
	{
		if(is_string($path))
		{
			$this->attributes['path'] = $path;
		}
		else
		{
			if(!empty($path))
			{
				$this->attributes['path'] = 'AdG'.round(microtime(true) * 1000).'_expenseDoc.'.$path->getClientOriginalExtension();
				$name = '/docs/purchase/AdG'.round(microtime(true) * 1000).'_expenseDoc.'.$path->getClientOriginalExtension();
				\Storage::disk('public')->put($name,\File::get($path));
			}
		}
	}

}
