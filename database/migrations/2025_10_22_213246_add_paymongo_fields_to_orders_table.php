<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('paymongo_payment_id')->nullable()->after('payment_method');
            $table->string('paymongo_payment_intent_id')->nullable()->after('paymongo_payment_id');
            $table->foreignId('transaction_id')->nullable()->after('paymongo_payment_intent_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['paymongo_payment_id', 'paymongo_payment_intent_id', 'transaction_id']);
        });
    }
};
