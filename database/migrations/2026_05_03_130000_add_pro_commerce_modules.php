<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('meta_title')->nullable()->after('description');
            $table->text('meta_description')->nullable()->after('meta_title');
        });

        Schema::create('product_variants', function (Blueprint $table): void {
            $table->id();
            $table->string('product_id');
            $table->string('name');
            $table->string('sku')->nullable();
            $table->decimal('price_delta', 10, 2)->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        Schema::create('inventory_adjustments', function (Blueprint $table): void {
            $table->id();
            $table->string('product_id');
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('old_stock');
            $table->integer('new_stock');
            $table->integer('delta');
            $table->string('reason')->default('manual');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        Schema::create('payment_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider')->default('stripe');
            $table->string('event_type');
            $table->string('status')->default('info');
            $table->string('reference')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            $table->string('variant_name')->nullable()->after('product_name');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->string('shipping_zone')->nullable()->after('shipping_address');
            $table->string('courier_name')->nullable()->after('shipping_zone');
            $table->string('tracking_number')->nullable()->after('courier_name');
            $table->timestamp('estimated_delivery_at')->nullable()->after('tracking_number');
            $table->text('payment_failure_reason')->nullable()->after('transaction_ref');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['shipping_zone', 'courier_name', 'tracking_number', 'estimated_delivery_at', 'payment_failure_reason']);
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('product_variant_id');
            $table->dropColumn('variant_name');
        });

        Schema::dropIfExists('payment_events');
        Schema::dropIfExists('inventory_adjustments');
        Schema::dropIfExists('product_variants');

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['meta_title', 'meta_description']);
        });
    }
};
