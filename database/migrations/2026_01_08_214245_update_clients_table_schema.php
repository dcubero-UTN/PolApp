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
        Schema::table('clients', function (Blueprint $table) {
            // Rename columns to English as per requirement
            $table->renameColumn('nombre_completo', 'name');
            $table->renameColumn('celular', 'phone_primary');
            //$table->renameColumn('telefono_fijo', 'phone_secondary'); // nullable below
            $table->renameColumn('direccion_señas', 'address_details');
            $table->renameColumn('dia_cobro', 'collection_day');
            $table->renameColumn('frecuencia_cobro', 'collection_frequency');

            // Drop old column if needed or just rename. telefono_fijo was nullable.
            // But we need to make sure type handles it. Rename is safe.
            $table->renameColumn('telefono_fijo', 'phone_secondary');

            // Rename correo to email? Wait, `email` is standard. `correo` was in initial migration. 
            // Initial migration had: string('correo')->nullable();
            $table->renameColumn('correo', 'email');

            // Add new columns
            $table->decimal('latitude', 10, 8)->nullable()->after('collection_frequency');
            $table->decimal('longitude', 10, 8)->nullable()->after('latitude');

            // Add SoftDeletes
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['latitude', 'longitude']);

            $table->renameColumn('name', 'nombre_completo');
            $table->renameColumn('phone_primary', 'celular');
            $table->renameColumn('phone_secondary', 'telefono_fijo');
            $table->renameColumn('address_details', 'direccion_señas');
            $table->renameColumn('collection_day', 'dia_cobro');
            $table->renameColumn('collection_frequency', 'frecuencia_cobro');
            $table->renameColumn('email', 'correo');
        });
    }
};
