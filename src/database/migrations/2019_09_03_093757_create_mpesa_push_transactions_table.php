<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMpesaPushTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mpesa_push_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('reference')->unique();
            $table->string('customer_msisdn');
            $table->double('amount');
            $table->string('business_number')->nullable();
            $table->timestamp('callback_received_at')->nullable();
            $table->string('callback_status')->nullable();
            $table->string('callback_description')->nullable();
            $table->string('mpesa_transaction_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mpesa_push_transactions');
    }
}
