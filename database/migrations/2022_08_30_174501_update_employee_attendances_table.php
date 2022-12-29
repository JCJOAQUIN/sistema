<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateEmployeeAttendancesTable extends Migration
{
    public function up()
    {
        Schema::table('employee_attendances', function (Blueprint $table)
		{
			$table->renameColumn('path','audit_trail_image_path');
		});
        Schema::table('employee_attendances', function (Blueprint $table)
		{
			$table->string('audit_trail_image_path')->nullable()->change();
			$table->string('low_quality_audit_trail_image_path')->nullable()->after('audit_trail_image_path');
			$table->string('face_scan_path')->nullable()->after('low_quality_audit_trail_image_path');
			$table->string('external_database_ref_id')->after('face_scan_path');
		});
    }

    public function down()
    {
        Schema::table('employee_attendances', function (Blueprint $table)
		{
			$table->dropColumn('low_quality_audit_trail_image_path')->nullable()->after('audit_trail_image_path');
			$table->dropColumn('face_scan_path')->nullable()->after('low_quality_audit_trail_image_path');
			$table->dropColumn('external_database_ref_id')->after('face_scan_path');
		});
        Schema::table('employee_attendances', function (Blueprint $table)
		{
			$table->renameColumn('audit_trail_image_path','path');
		});
    }
}
