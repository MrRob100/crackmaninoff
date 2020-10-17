<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTunesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tunes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('plays');
            $table->unsignedBigInteger('page_id');
            $table->integer('start');
            $table->integer('end');
            $table->timestamps();

            $table->foreign('page_id')->references('id')
                ->on('pages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tunes');
    }
}
