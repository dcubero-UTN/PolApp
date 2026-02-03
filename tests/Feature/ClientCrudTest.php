<?php

namespace Tests\Feature;

use App\Livewire\ClientIndex;
use App\Livewire\ClientForm;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClientCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_clients_can_be_created()
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('vendedor');

        Livewire::actingAs($user)
            ->test(ClientForm::class)
            ->set('name', 'Test Client')
            ->set('phone_primary', '8888-8888')
            ->set('address_details', 'Test Address')
            ->set('collection_day', 'Lunes')
            ->set('collection_frequency', 'Semanal')
            ->call('save')
            ->assertRedirect(route('clients.index'));

        $this->assertDatabaseHas('clients', [
            'name' => 'Test Client',
            'phone_primary' => '8888-8888',
        ]);
    }

    public function test_clients_validation_unique_phone()
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('vendedor');

        Client::create([
            'user_id' => $user->id,
            'name' => 'Existing Client',
            'phone_primary' => '8888-8888',
            'address_details' => 'Address',
            'collection_day' => 'Lunes',
            'collection_frequency' => 'Semanal',
        ]);

        Livewire::actingAs($user)
            ->test(ClientForm::class)
            ->set('name', 'New Client')
            ->set('phone_primary', '8888-8888') // Duplicate
            ->set('address_details', 'Address')
            ->set('collection_day', 'Lunes')
            ->set('collection_frequency', 'Semanal')
            ->call('save')
            ->assertHasErrors(['phone_primary']);
    }

    public function test_clients_soft_delete()
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $client = Client::create([
            'user_id' => $admin->id,
            'name' => 'Client To Delete',
            'phone_primary' => '9999-9999',
            'address_details' => 'Address',
            'collection_day' => 'Lunes',
            'collection_frequency' => 'Semanal',
        ]);

        Livewire::actingAs($admin)
            ->test(ClientIndex::class)
            ->call('delete', $client->id);

        $this->assertSoftDeleted($client);
    }
}
