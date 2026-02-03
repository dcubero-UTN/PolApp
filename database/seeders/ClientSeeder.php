<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\User;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendedor = User::role('vendedor')->first();

        if (!$vendedor) {
            $this->command->error('No se encontró un usuario con el rol vendedor. Ejecute DatabaseSeeder primero.');
            return;
        }

        $days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

        $clientsData = [
            'Lunes' => [
                ['name' => 'Juan Pérez', 'phone' => '88001122', 'address' => 'Barrio Luján, 100m Este de la iglesia'],
                ['name' => 'María Rodríguez', 'phone' => '88334455', 'address' => 'Zapote, frente al redondel'],
            ],
            'Martes' => [
                ['name' => 'Carlos Vargas', 'phone' => '88556677', 'address' => 'Tres Ríos, Residencial La Floresta'],
                ['name' => 'Lucía Méndez', 'phone' => '88112233', 'address' => 'Curridabat, San Jerónimo'],
            ],
            'Miércoles' => [
                ['name' => 'Roberto Gómez', 'phone' => '87112233', 'address' => 'Desamparados, Centro'],
                ['name' => 'Ana Cascante', 'phone' => '89223344', 'address' => 'San Rafael Arriba'],
            ],
            'Jueves' => [
                ['name' => 'Felipe Mora', 'phone' => '83445566', 'address' => 'Heredia, San Francisco'],
                ['name' => 'Elena Solano', 'phone' => '84556677', 'address' => 'San Joaquín de Flores'],
            ],
            'Viernes' => [
                ['name' => 'David Chinchilla', 'phone' => '85667788', 'address' => 'Alajuela, La Guácima'],
                ['name' => 'Sofía Rojas', 'phone' => '86778899', 'address' => 'San Antonio de Belén'],
            ],
            'Sábado' => [
                ['name' => 'Jorge Blanco', 'phone' => '81990011', 'address' => 'Escazú, Guachipelín'],
                ['name' => 'Marta Quirós', 'phone' => '82001122', 'address' => 'Santa Ana, Lindora'],
            ],
            'Domingo' => [
                ['name' => 'Ricardo Alfaro', 'phone' => '80112233', 'address' => 'Pavas, Rohrmoser'],
            ],
        ];

        foreach ($clientsData as $day => $clients) {
            foreach ($clients as $index => $data) {
                Client::create([
                    'user_id' => $vendedor->id,
                    'name' => $data['name'],
                    'phone_primary' => $data['phone'],
                    'address_details' => $data['address'],
                    'collection_day' => $day,
                    'collection_frequency' => 'Semanal',
                    'hora_cobro' => str_pad(8 + $index, 2, '0', STR_PAD_LEFT) . ':00', // 08:00, 09:00, etc
                    'current_balance' => 0,
                ]);
            }
        }

        $this->command->info('✅ Clientes creados para todos los días de la semana.');
    }
}
