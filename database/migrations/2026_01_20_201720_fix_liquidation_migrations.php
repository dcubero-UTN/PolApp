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
        // 1. Create Liquidations Table
        Schema::create('liquidations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->date('date');

            // Financial Summary
            $table->decimal('total_recaudacion', 12, 2)->default(0);
            $table->decimal('total_gastos', 12, 2)->default(0);
            $table->decimal('total_efectivo', 12, 2)->default(0);
            $table->decimal('total_transferencia', 12, 2)->default(0);
            $table->decimal('total_a_entregar', 12, 2)->default(0);

            // KPIs
            $table->integer('clientes_visitados')->default(0);
            $table->integer('clientes_pagaron')->default(0);
            $table->decimal('efectividad', 5, 2)->default(0);
            $table->decimal('ventas_nuevas', 12, 2)->default(0);

            // Status
            $table->enum('status', ['pendiente', 'confirmada'])->default('pendiente');
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users');

            $table->timestamps();
        });

        // 2. Add columns to existing tables (Check if they already exist due to partial failure)
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'liquidation_id')) {
                $table->foreignId('liquidation_id')->nullable()->constrained('liquidations')->onDelete('set null');
            }
        });

        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'liquidation_id')) {
                $table->foreignId('liquidation_id')->nullable()->constrained('liquidations')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['liquidation_id']);
            $table->dropColumn('liquidation_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['liquidation_id']);
            $table->dropColumn('liquidation_id');
        });

        Schema::dropIfExists('liquidations');
    }
};
