<?php

namespace Tests\Feature;

use App\Livewire\ClientIndex;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClientSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_scope_filters_by_name()
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('vendedor');

        Client::create(['user_id' => $user->id, 'name' => 'Juan Perez', 'phone_primary' => '1111', 'collection_day' => 'Lunes']);
        Client::create(['user_id' => $user->id, 'name' => 'Maria Gomez', 'phone_primary' => '2222', 'collection_day' => 'Martes']);

        Livewire::actingAs($user)
            ->test(ClientIndex::class)
            ->set('search', 'Juan')
            ->assertSee('Juan Perez')
            ->assertDontSee('Maria Gomez');
    }

    public function test_day_scope_filters_by_day()
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('vendedor');

        Client::create(['user_id' => $user->id, 'name' => 'Lunes Client', 'collection_day' => 'Lunes']);
        Client::create(['user_id' => $user->id, 'name' => 'Martes Client', 'collection_day' => 'Martes']);

        // Test Lunes
        Livewire::actingAs($user)
            ->test(ClientIndex::class)
            ->set('collection_day', 'Lunes')
            ->assertSee('Lunes Client')
            ->assertDontSee('Martes Client');
    }

    public function test_default_day_is_selected_on_mount()
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('vendedor');

        // We can't easily predict 'today' in test environment without mocking time, 
        // but we can check if collection_day is set to something valid.

        $component = Livewire::actingAs($user)->test(ClientIndex::class);

        // It should default to today's day name in Spanish
        $daysMap = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            0 => 'Domingo'
        ];
        $today = $daysMap[date('w')];

        $component->assertSet('collection_day', $today);
    }
}
