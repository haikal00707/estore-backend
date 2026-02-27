<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            if (!Schema::hasColumn('cart_items', 'cart_id')) {
                $table->foreignId('cart_id')->after('id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('cart_items', 'product_id')) {
                $table->foreignId('product_id')->after('cart_id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('cart_items', 'quantity')) {
                $table->integer('quantity')->after('product_id')->default(1);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['cart_id']);
            $table->dropForeign(['product_id']);
            $table->dropColumn(['cart_id', 'product_id', 'quantity']);
        });
    }
};
