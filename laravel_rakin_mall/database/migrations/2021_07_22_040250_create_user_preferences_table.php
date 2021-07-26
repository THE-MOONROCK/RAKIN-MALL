<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usrpref', function (Blueprint $table) {
            $table->id();
            $table->integer('usr_id')->unsigned();
            $table->string('locale',10)->nullable();
            $table->string('direction',10)->nullable();
            $table->string('color_theme',20)->nullable();

            $table->timestamps();
            $table->softDeletes();
            // foreign key
            $table->foreign('usr_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usrpref');
    }
}
