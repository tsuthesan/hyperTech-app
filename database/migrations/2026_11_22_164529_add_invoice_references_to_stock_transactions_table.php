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
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->foreignId('invoice_id')
                ->nullable()
                ->after('product_id')
                ->constrained('invoices')
                ->nullOnDelete();

            $table->foreignId('invoice_item_id')
                ->nullable()
                ->after('invoice_id')
                ->unique()
                ->constrained('invoice_items')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropForeign(['invoice_item_id']);
            $table->dropUnique(['invoice_item_id']);
            $table->dropColumn(['invoice_id', 'invoice_item_id']);
        });
    }
};
