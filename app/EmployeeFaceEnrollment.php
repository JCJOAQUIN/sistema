<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeFaceEnrollment extends Model
{
	protected $fillable = [
		'employee_id',
		'audit_trail_image_path',
		'low_quality_audit_trail_image_path',
		'face_scan_path',
		'external_database_ref_id'
	];
}
