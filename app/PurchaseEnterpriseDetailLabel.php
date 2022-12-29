<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseEnterpriseDetailLabel extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idPurchaseEnterpriseDetailLabel';
	protected $fillable   = 
	[
		'idlabels',
		'idPurchaseEnterpriseDetail',
	];

	public function label()
	{
		return $this->belongsTo(Label::class,'idlabels','idlabels');
	}
}
