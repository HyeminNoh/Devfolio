<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContributionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contributions', function (Blueprint $table) {
            $table->id('idx');
            $table->bigInteger('user_idx')->unsigned()->index();
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
        Schema::dropIfExists('contributions');
    }
}
