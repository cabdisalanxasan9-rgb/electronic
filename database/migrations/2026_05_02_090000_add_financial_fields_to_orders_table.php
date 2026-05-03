<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('discount_amount', 10, 2)->default(0)->after('sub_total');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('shipping_amount');
            $table->string('coupon_code')->nullable()->after('transaction_ref');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['discount_amount', 'tax_amount', 'coupon_code']);
        });
    }
};
