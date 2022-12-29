<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExpensesDocuments extends Model
{
	protected $primaryKey = 'idExpensesDocuments';
	protected $fillable   = 
	[
		'name',
		'path',
		'date',
		'idExpensesDetail',
		'fiscal_folio',
		'timepath',
		'ticket_number',
		'amount',
		'users_id',
	];

	public function setPathAttribute($path)
	{
		if(is_string($path))
		{
			$this->attributes['path'] = $path;
		}
		else
		{
			$this->attributes['path'] = 'AdG'.round(microtime(true) * 1000).'_expenseDoc.'.$path->getClientOriginalExtension();
			$name = '/docs/expenses/AdG'.round(microtime(true) * 1000).'_expenseDoc.'.$path->getClientOriginalExtension();
			\Storage::disk('public')->put($name,\File::get($path));
		}
	}
}
