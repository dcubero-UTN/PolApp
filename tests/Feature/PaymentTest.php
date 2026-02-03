<?php

namespace Tests\Feature;

use App\Livewire\PaymentModal;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_updates_balances_and_closes_sale()
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
            'status' => 'pendiente',
            'suggested_quota' => 500
        ]);

        // Open Modal - Should preload 500
        Livewire::actingAs($seller)
            ->test(PaymentModal::class)
            ->call('openPaymentModal', $client->id)
            ->assertSet('amount', 500)
            ->assertSet('whatsappLink', null)
            // Pay 500
            ->call('savePayment')
            ->assertSet('whatsappLink', function ($val) {
                return str_contains($val, 'wa.me/50688888888') && str_contains($val, '500');
            });

        // Verify Balances
        $this->assertEquals(500, $sale->fresh()->current_balance);
        $this->assertEquals(500, $client->fresh()->current_balance);
        $this->assertEquals('pendiente', $sale->fresh()->status);

        // Pay remaining 500
        Livewire::actingAs($seller)
            ->test(PaymentModal::class)
            ->call('openPaymentModal', $client->id)
            ->set('amount', 500)
            ->call('savePayment');

        // Verify Closed
        $this->assertEquals(0, $sale->fresh()->current_balance);
        $this->assertEquals('pagado', $sale->fresh()->status);

        // Check Payment Records
        $this->assertCount(2, Payment::all());
    }

    public function test_payment_validation()
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $seller = User::factory()->create();
        $seller->assignRole('vendedor');

        $client = Client::create(['user_id' => $seller->id, 'name' => 'Debtor', 'phone_primary' => '88888888', 'current_balance' => 100]);
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
            ->set('amount', 200) // More than balance
            ->call('savePayment')
            ->assertHasErrors(['amount']);
    }

    public function test_sinpe_payment_with_reference()
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $seller = User::factory()->create();
        $seller->assignRole('vendedor');

        $client = Client::create(['user_id' => $seller->id, 'name' => 'Debtor', 'phone_primary' => '88888888', 'current_balance' => 500]);
        $sale = Sale::create([
            'client_id' => $client->id,
            'user_id' => $seller->id,
            'total_amount' => 500,
            'initial_downpayment' => 0,
            'current_balance' => 500,
        ]);

        Livewire::actingAs($seller)
            ->test(PaymentModal::class)
            ->call('openPaymentModal', $client->id)
            ->set('payment_method', 'sinpe')
            ->set('reference_number', '1234')
            ->set('amount', 500)
            ->call('savePayment');

        // Verify SINPE payment was recorded
        $payment = Payment::first();
        $this->assertEquals('sinpe', $payment->payment_method);
        $this->assertEquals('1234', $payment->reference_number);
    }
}
