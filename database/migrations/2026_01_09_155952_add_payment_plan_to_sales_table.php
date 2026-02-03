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
        Schema::table('sales', function (Blueprint $table) {
            $table->integer('number_of_installments')->default(8)->after('current_balance');
            $table->decimal('suggested_quota', 12, 2)->default(0)->after('number_of_installments');
            $table->enum('quota_period', ['semanal', 'quincenal', 'mensual'])->default('semanal')->after('suggested_quota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['number_of_installments', 'suggested_quota', 'quota_period']);
        });
    }
};
