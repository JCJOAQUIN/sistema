<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPermissionTypeColumnToModulesTable extends Migration
{
    public function up()
    {
        Schema::table('modules', function(Blueprint $table) 
        {
            $table->integer('permission_type')->nullable()->comment('1. Permiso normal || 2.Permiso por Empresa y Departamento || 3. Permiso por Proyecto || 4. Permiso Por Proyecto y Tipo de RQ || 5. Permiso para cargar documentos (SGI) || 6. Permiso por Proyecto y carga de documento de calidad');
        });
    }

    public function down()
    {
        Schema::table('modules', function(Blueprint $table) 
        {
            $table->dropColumn('permission_type');
        });
    }
}
