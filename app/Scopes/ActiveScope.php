<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ActiveScope implements Scope
{
	/**
	 * Apply the scope to a given Eloquent query builder.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $builder
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 * @return void
	 */
	public function apply(Builder $builder, Model $model)
	{
		$builder->whereRaw('DATE_FORMAT(validity_start, "%Y-%m-%d 00:00:00") <= NOW()')
			->where(function($q)
			{
				$q->whereNull('validity_end')
					->orWhereRaw('DATE_FORMAT(validity_end, "%Y-%m-%d 23:59:59") >= NOW()');
			});
	}
}