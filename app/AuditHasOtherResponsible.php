<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuditHasOtherResponsible extends Model
{
	protected $fillable =
	[
		'name',
		'audit_id'
	];
}
