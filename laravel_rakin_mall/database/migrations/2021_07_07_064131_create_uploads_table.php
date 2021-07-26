<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUploadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uploads', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid')->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->string('module')->nullable();
            $table->integer('module_id')->nullable();
            $table->uuid('upload_token')->nullable();
            $table->string('user_filename')->nullable();
            $table->string('filename')->nullable();
            $table->boolean('is_temp_delete')->default(0);
            $table->boolean('status')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uploads');
    }
}