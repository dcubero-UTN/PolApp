<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            ProductSeeder::class,
            ClientSeeder::class,
            SaleSeeder::class,
        ]);

        // Create admin user
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@polaapp.com',
            'password' => bcrypt('admin123'),
        ]);
        $admin->assignRole('admin');

        // Create seller user
        $seller = User::create([
            'name' => 'Vendedor Demo',
            'email' => 'vendedor@polaapp.com',
            'password' => bcrypt('vendedor123'),
        ]);
        $seller->assignRole('vendedor');

        $this->command->info('✅ Admin: admin@polaapp.com / admin123');
        $this->command->info('✅ Vendedor: vendedor@polaapp.com / vendedor123');
    }
}
