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
        Schema::create('daily_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Vendedor
            $table->foreignId('client_id')->constrained();
            $table->date('visit_date'); // Fecha de la ruta
            $table->boolean('completed')->default(false); // Se vuelve true al cobrar o dejar nota
            $table->enum('result', ['abono', 'incumplimiento', 'devolucion'])->nullable();
            $table->timestamps();

            // Un vendedor no puede visitar al mismo cliente dos veces el mismo día (lógicamente)
            $table->unique(['user_id', 'client_id', 'visit_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_visits');
    }
};
