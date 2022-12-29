<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LabelDetailExpenses extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idlabelDetailExpenses';
	protected $fillable   = 
	[
		'idlabels',
		'idExpensesDetail',
	];

	public function label()
	{
		return $this->belongsTo(Label::class,'idlabels','idlabels');
	}
}
