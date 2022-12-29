<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToRealEmployees extends Migration
{
    public function up()
    {
        Schema::table('real_employees', function (Blueprint $table) 
        {
            $table->string('doc_birth_certificate',250)->nullable();
            $table->string('doc_proof_of_address',250)->nullable();
            $table->string('doc_nss',250)->nullable();
            $table->string('doc_ine',250)->nullable();
            $table->string('doc_curp',250)->nullable();
            $table->string('doc_rfc',250)->nullable();
            $table->string('doc_cv',250)->nullable();
            $table->string('doc_proof_of_studies',250)->nullable();
            $table->string('doc_professional_license',250)->nullable();
        });
    }

    public function down()
    {
        Schema::table('real_employees', function (Blueprint $table) 
        {
            $table->dropColumn('doc_birth_certificate');
            $table->dropColumn('doc_proof_of_address');
            $table->dropColumn('doc_nss');
            $table->dropColumn('doc_ine');
            $table->dropColumn('doc_curp');
            $table->dropColumn('doc_rfc');
            $table->dropColumn('doc_cv');
            $table->dropColumn('doc_proof_of_studies');
            $table->dropColumn('doc_professional_license');
        });
    }
}
