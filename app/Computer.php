<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Computer extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idComputer';
	protected $fillable   = 
	[
		'idComputer',
		'idFolio',
		'idKind',
		'role_id',
		'title',
		'datetitle',
		'entry',
		'entry_date',
		'device',
		'kind_account',
		'email_account',
		'alias_account',
		'iva',
		'subtotal',
		'total',
		'idComputerEquipment',
		'idDetailPurchase',
	];

	public function software()
	{
		return $this->belongsToMany(Software::class,'computer_software','idComputer','idSoftware');
	}

	public function DetailComputer()
	{
		return $this->hasMany(DetailComputer::class,'idComputer','idComputer');
	}

	public function requestModel()
	{
		return $this->belongsTo(RequestModel::class,'idFolio','folio');
	}

	public function provider()
	{
		return $this->belongsTo(Provider::class,'idProvider','idProvider');
	}

	public function computerAccounts()
	{
		return $this->hasMany(ComputerEmailsAccounts::class,'idComputer','idComputer');
	}
}