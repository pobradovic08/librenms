<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNetappDfTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('netapp_df', function (Blueprint $table) {
            $table->increments('df_id');
            $table->string('file_sys', 256);
            $table->string('mounted_on', 128)->nullable();
            $table->string('vserver', 128)->nullable();
            $table->string('index', 128)->nullable();
            $table->unsignedInteger('kbytes_percent')->default(0);
            $table->unsignedInteger('inode_percent')->default(0);
            $table->unsignedInteger('max_files_possible')->default(0);
            $table->unsignedInteger('saved_percent')->default(0);
            $table->unsignedInteger('compress_saved_percent')->default(0);
            $table->unsignedInteger('dedupe_percent')->default(0);
            $table->unsignedInteger('total_saved_percent')->default(0);
            $table->unsignedInteger('kbytes_reserved')->default(0);
            $table->unsignedInteger('kbytes_total')->default(0);
            $table->string('online');
            $table->string('status');
            $table->string('mirror_status');
            $table->string('type');
            $table->unsignedInteger('device_id')->index('device_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('netapp_df');
    }
}
