<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RefundRetentions extends Model
{	
	protected $fillable = ['idRefundRetentions','name','amount','idRefundDetail'];

	public $timestamps = false;

	protected $primaryKey = 'idrefundRetentions';
}
