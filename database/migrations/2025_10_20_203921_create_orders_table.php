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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Pickup details
            $table->date('pickup_date');
            $table->time('pickup_time');
            $table->text('pickup_address');
            
            // Order details
            $table->boolean('is_rush_service')->default(false);
            $table->text('special_instructions')->nullable();
            $table->string('promo_code')->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('rush_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            
            // Order status
            $table->enum('status', [
                'pending',
                'in_transit',
                'picked_up',
                'processing',
                'ready',
                'out_for_delivery',
                'completed',
                'cancelled'
            ])->default('pending');
            
            // Payment details
            $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->timestamp('paid_at')->nullable();
            
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
        Schema::dropIfExists('orders');
    }
};
