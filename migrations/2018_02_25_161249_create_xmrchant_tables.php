<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXmrchantTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function(Blueprint $table) {
            $table->unsignedInteger('id');
            $table->unsignedInteger('user_id');

            $table->primary('id');
            $table->unique(['id', 'user_id']);
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->string('id');
            $table->unsignedInteger('account_id');
            $table->unsignedInteger('subaddress_id');
            $table->enum('type', ['in', 'out', 'pool']);
            $table->bigInteger('amount');
            $table->bigInteger('fee');
            $table->bigInteger('height');
            $table->bigInteger('timestamp');
            $table->timestamps();

            $table->foreign('account_id')
                ->references('id')
                ->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('accounts');
    }
}
