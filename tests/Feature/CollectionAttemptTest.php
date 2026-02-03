<?php

namespace Tests\Feature;

use App\Livewire\PaymentModal;
use App\Models\Client;
use App\Models\CollectionAttempt;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CollectionAttemptTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_payment_visit_is_recorded()
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $seller = User::factory()->create();
        $seller->assignRole('vendedor');

        $client = Client::create(['user_id' => $seller->id, 'name' => 'Debtor', 'phone_primary' => '88888888', 'current_balance' => 1000]);

        $sale = Sale::create([
            'client_id' => $client->id,
            'user_id' => $seller->id,
            'total_amount' => 1000,
            'initial_downpayment' => 0,
            'current_balance' => 1000,
        ]);

        Livewire::actingAs($seller)
            ->test(PaymentModal::class)
            ->call('openPaymentModal', $client->id)
            ->call('toggleNoPaymentForm')
            ->assertSet('showNoPaymentForm', true)
            ->set('attempt_reason', 'no_estaba')
            ->set('attempt_notes', 'Casa cerrada, nadie respondió')
            ->set('attempt_latitude', 9.748917)
            ->set('attempt_longitude', -83.753428)
            ->call('saveNoPayment');

        // Verify Collection Attempt was recorded
        $this->assertDatabaseHas('collection_attempts', [
            'client_id' => $client->id,
            'user_id' => $seller->id,
            'reason' => 'no_estaba',
            'notes' => 'Casa cerrada, nadie respondió',
        ]);

        // Verify balance NOT changed
        $this->assertEquals(1000, $sale->fresh()->current_balance);
        $this->assertEquals(1000, $client->fresh()->current_balance);
    }

    public function test_collection_attempt_validation()
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $seller = User::factory()->create();
        $seller->assignRole('vendedor');

        $client = Client::create(['user_id' => $seller->id, 'name' => 'Debtor', 'phone_primary' => '88888888']);
        $sale = Sale::create([
            'client_id' => $client->id,
            'user_id' => $seller->id,
            'total_amount' => 100,
            'initial_downpayment' => 0,
            'current_balance' => 100,
        ]);

        Livewire::actingAs($seller)
            ->test(PaymentModal::class)
            ->call('openPaymentModal', $client->id)
            ->call('toggleNoPaymentForm')
            ->set('attempt_reason', '') // Empty reason
            ->call('saveNoPayment')
            ->assertHasErrors(['attempt_reason']);
    }
}
