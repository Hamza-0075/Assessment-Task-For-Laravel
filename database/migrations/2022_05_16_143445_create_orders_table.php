<?php

use App\Models\Order;
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
            $table->foreignId('merchant_id')->constrained();
            $table->foreignId('affiliate_id')->nullable()->constrained();
             // Using decimal instead of float to avoid precision issues in financial calculations especially for money or percentage because decinmal store exact fixed point numbers.
            $table->decimal('subtotal');
            $table->decimal('commission_owed')->default(0.00);
            $table->string('payout_status')->default(Order::STATUS_UNPAID);
            $table->string('discount_code')->nullable();
            $table->string('external_order_id')->unique()->nullable();
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
