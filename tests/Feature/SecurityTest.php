<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_rules()
    {
        // Setup Roles (Seeders are not automatically run in RefreshDatabase unless specified, 
        // but we can just run the seeder logic or manually create roles here for speed)
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create(['name' => 'Admin']);
        $admin->assignRole('admin');

        $vendorA = User::factory()->create(['name' => 'Vendor A']);
        $vendorA->assignRole('vendedor');

        $vendorB = User::factory()->create(['name' => 'Vendor B']);
        $vendorB->assignRole('vendedor');

        // Create Clients
        $clientA = Client::create([
            'user_id' => $vendorA->id,
            'nombre_completo' => 'Client A',
            'celular' => '1234',
            'direccion_señas' => 'Address A',
            'dia_cobro' => 'Lunes',
            'frecuencia_cobro' => 'Semanal',
        ]);

        $clientB = Client::create([
            'user_id' => $vendorB->id,
            'nombre_completo' => 'Client B',
            'celular' => '5678',
            'direccion_señas' => 'Address B',
            'dia_cobro' => 'Martes',
            'frecuencia_cobro' => 'Semanal',
        ]);

        // TEST 1: Global Scope (Vendor A sees only Client A)
        $this->actingAs($vendorA);
        $clientsForA = Client::all();
        $this->assertTrue($clientsForA->contains($clientA), 'Vendor A should see Client A');
        $this->assertFalse($clientsForA->contains($clientB), 'Vendor A should NOT see Client B');

        // TEST 2: Admin Scope (Admin sees all)
        $this->actingAs($admin);
        $allClients = Client::all();
        $this->assertEquals(2, $allClients->count(), 'Admin should see 2 clients');

        // TEST 3: Policy - View (Vendor A cannot view Client B)
        $this->actingAs($vendorA);
        $this->assertTrue($vendorA->can('view', $clientA), 'Vendor A can view own client');
        $this->assertFalse($vendorA->can('view', $clientB), 'Vendor A cannot view other client');

        // TEST 4: Policy - Delete (Vendor A cannot delete Client A)
        $this->assertFalse($vendorA->can('delete', $clientA), 'Vendor A cannot delete own client');
        $this->assertTrue($admin->can('delete', $clientA), 'Admin can delete client');

        echo "\nSecurity Rules Verified Successfully!\n";
    }
}
