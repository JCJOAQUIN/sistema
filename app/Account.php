<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
	protected $primaryKey = 'idAccAcc';
	public $timestamps    = false;
	protected $fillable   = 
	[
		'account',
		'description',
		'balance',
		'selectable',
		'content',
		'idEnterprise',
		'identifier'
	];

	public function getFatherAttribute()
	{
		$accountTemp = $this->account;
		if(substr($accountTemp, 4)!='000')
		{
			return substr($accountTemp, 0,4).'000';
		}
		elseif (substr($accountTemp,2,2)!='00')
		{
			return substr($accountTemp, 0, 2).'00000';
		}
		elseif (substr($accountTemp, 1,1)!='0')
		{
			return substr($accountTemp, 0, 1).'000000';
		}
		else
		{
			return "";
		}
	}

	public function getLevelAttribute()
	{
		$accountTemp = $this->account;
		if(substr($accountTemp, 4)!='000')
		{
			return '4';
		}
		elseif (substr($accountTemp,2,2)!='00')
		{
			return '3';
		}
		elseif (substr($accountTemp, 1,1)!='0')
		{
			return '2';
		}
		else
		{
			return '1';
		}
	}

	public function enterprise()
	{
		return $this->belongsTo(Enterprise::class,'idEnterprise','id');
	}

	public function request()
	{
		return $this->hasMany(RequestModel::class,'account','idAccAcc');
	}

	public function requestReview()
	{
		return $this->hasMany(RequestModel::class,'accountR','idAccAcc');
	}

	public function resource()
	{
		return $this->belongsTo(ResourceDetail::class,'idAccAcc','idAccAcc');
	}

	public function fullClasificacionName()
	{
		return $this->account . ' - ' . $this->description.' ('.$this->content.')';
	}

	public function scopeOrderNumber($query)
	{
		return $query->orderBy('account','asc');
	}
}
