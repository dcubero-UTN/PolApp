<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 12, 2)->after('unit_price')->nullable();
        });

        // Backfill existing items with current product cost
        DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->update(['sale_items.unit_cost' => DB::raw('products.cost_price')]);

        // Make it non-nullable after backfill if desired, or keep as is.
        // For safety during migration, we'll just leave it.
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('unit_cost');
        });
    }
};
