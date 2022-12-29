<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuditHasOtherAuditor extends Model
{
	protected $fillable =
	[
		'name',
		'type',
		'audit_id'
	];
}
