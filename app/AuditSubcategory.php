<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuditSubcategory extends Model
{
	protected $table 	= 'audit_subcategories';

	protected $fillable = [
		'id','name','audit_category_id'
	];
}
