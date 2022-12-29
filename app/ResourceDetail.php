<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResourceDetail extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idresourcedetail';
	protected $fillable   = 
	[
		'concept',
		'amount',
		'idresource',
		'idAccAcc',
		'idAccAccR',
		'statusRefund',
	];

	public function resource()
	{
		return $this->belongsTo(Resource::class,'idresource','idresource');
	}

	public function accounts()
	{
		return $this->belongsTo(Account::class,'idAccAcc','idAccAcc');
	}

	public function accountsReview()
	{
		return $this->belongsTo(Account::class,'idAccAccR','idAccAcc');
	}
}
