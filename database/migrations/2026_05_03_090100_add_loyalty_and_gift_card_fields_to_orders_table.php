<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->unsignedInteger('loyalty_points_used')->default(0)->after('coupon_code');
            $table->decimal('loyalty_discount_amount', 10, 2)->default(0)->after('loyalty_points_used');
            $table->string('gift_card_code')->nullable()->after('loyalty_discount_amount');
            $table->decimal('gift_card_amount', 10, 2)->default(0)->after('gift_card_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'loyalty_points_used',
                'loyalty_discount_amount',
                'gift_card_code',
                'gift_card_amount',
            ]);
        });
    }
};
