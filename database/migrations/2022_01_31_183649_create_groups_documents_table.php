<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupsDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('groups_documents', function (Blueprint $table)
		{
			$table->increments('idgroupsDocuments');
			$table->text('path')->nullable();
			$table->timestamp('date')->nullable();
			$table->integer('idgroups')->unsigned();
			$table->timestamp('updated_at')->nullable();
			$table->foreign('idgroups')->references('idgroups')->on('groups');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('groups_documents');
	}
}
