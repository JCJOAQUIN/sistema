<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeAttendance extends Model
{
	protected $fillable   = 
	[
		'employee_id',
		'latitude',
		'longitude',
		'audit_trail_image_path',
		'low_quality_audit_trail_image_path',
		'face_scan_path'
	];
}
