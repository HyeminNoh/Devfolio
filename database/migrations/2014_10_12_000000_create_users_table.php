<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('github_id');
            $table->string('avatar');
            $table->string('access_token');
            $table->string('github_url');
            $table->string('blog_url')->nullable();
            $table->dateTime('created_dt')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_dt')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
