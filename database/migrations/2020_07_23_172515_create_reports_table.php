<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_idx')->unsigned()->index();
            $table->string('type');
            $table->json('data');
            $table->dateTime('created_dt')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_dt')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->foreign('user_idx')->references('idx')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports');
    }
}
