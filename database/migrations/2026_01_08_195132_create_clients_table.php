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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->string('nombre_completo');
            $table->string('telefono_fijo')->nullable();
            $table->string('celular');
            $table->string('correo')->nullable();
            $table->text('direccion_señas');
            $table->string('dia_cobro')->index(); // Lunes, Martes, etc.
            $table->time('hora_cobro')->nullable();
            $table->enum('frecuencia_cobro', ['Diario', 'Semanal', 'Quincenal', 'Mensual']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
