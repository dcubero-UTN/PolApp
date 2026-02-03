<?php

namespace Tests\Feature;

use App\Livewire\SaleForm;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_reduces_stock_and_increases_balance()
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $seller = User::factory()->create();
        $seller->assignRole('vendedor');

        $client = Client::create(['user_id' => $seller->id, 'name' => 'Buyer', 'phone_primary' => '999']);
        $product = Product::create([
            'sku' => 'SKU1',
            'name' => 'P1',
            'cost_price' => 100,
            'sale_price' => 200,
            'stock' => 10
        ]);

        Livewire::actingAs($seller)
            ->test(SaleForm::class)
            ->call('selectClient', $client->id, $client->name)
            ->call('addToCart', $product->id) // Adds 1 qty
            ->call('save');

        // Verify Data
        $this->assertDatabaseHas('sales', [
            'client_id' => $client->id,
            'total_amount' => 200,
            'current_balance' => 200, // 0 downpayment
        ]);

        $this->assertDatabaseHas('sale_items', [
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 200,
        ]);

        // Verify Stock
        $this->assertEquals(9, $product->fresh()->stock);

        // Verify Client Balance
        $this->assertEquals(200, $client->fresh()->current_balance);
    }

    public function test_cannot_sell_without_stock()
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $seller = User::factory()->create();
        $seller->assignRole('vendedor');

        $client = Client::create(['user_id' => $seller->id, 'name' => 'Buyer', 'phone_primary' => '999']);
        $product = Product::create([
            'sku' => 'SKU2',
            'name' => 'P2',
            'cost_price' => 100,
            'sale_price' => 200,
            'stock' => 0
        ]);

        Livewire::actingAs($seller)
            ->test(SaleForm::class)
            ->call('selectClient', $client->id, $client->name)
            ->call('addToCart', $product->id)
            ->call('save')
            ->assertHasErrors(); // Should fail validation 'cart' min:1 because addToCart checks stock

        // If we force it into cart (mocking)
        // logic protects against it? addToCart checks stock.
        // What if stock was 1 when added, but 0 when saved (race condition)? 
        // Logic inside transaction checks again.
    }

    public function test_downpayment_calcs()
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $seller = User::factory()->create();
        $seller->assignRole('vendedor');

        $client = Client::create(['user_id' => $seller->id, 'name' => 'Buyer', 'phone_primary' => '999']);
        $product = Product::create([
            'sku' => 'SKU3',
            'name' => 'P3',
            'cost_price' => 100,
            'sale_price' => 200,
            'stock' => 10
        ]);

        Livewire::actingAs($seller)
            ->test(SaleForm::class)
            ->call('selectClient', $client->id, $client->name)
            ->call('addToCart', $product->id)
            ->set('initial_downpayment', 50)
            ->assertSet('current_balance', 150)
            ->call('save');

        $this->assertDatabaseHas('sales', [
            'initial_downpayment' => 50,
            'current_balance' => 150,
        ]);

        $this->assertEquals(150, $client->fresh()->current_balance);
    }

    public function test_quota_calculation()
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $seller = User::factory()->create();
        $seller->assignRole('vendedor');

        $client = Client::create(['user_id' => $seller->id, 'name' => 'Buyer', 'phone_primary' => '999']);
        $product = Product::create([
            'sku' => 'SKU4',
            'name' => 'P4',
            'cost_price' => 100,
            'sale_price' => 2000,
            'stock' => 10
        ]);

        // Sale: 2000. Downpayment: 0. Balance: 2000. Installments: 8.
        // Quota = 2000 / 8 = 250.
        // Round to nearest 500 = 500? 
        // 250 is exactly half. Round usually rounds up at .5 for integers, but here we round on a scale.
        // round(250/500) = round(0.5) = 1. 1*500 = 500.

        Livewire::actingAs($seller)
            ->test(SaleForm::class)
            ->call('selectClient', $client->id, $client->name)
            ->call('addToCart', $product->id)
            ->set('number_of_installments', 8)
            ->assertSet('suggested_quota', 500)
            ->set('number_of_installments', 2) // 2000 / 2 = 1000.
            ->assertSet('suggested_quota', 1000)
            // Test Manual Override
            ->set('suggested_quota', 1200)
            ->call('save');

        $this->assertDatabaseHas('sales', [
            'total_amount' => 2000,
            'number_of_installments' => 2,
            'suggested_quota' => 1200, // Manual override persisted
            'quota_period' => 'semanal',
        ]);
    }
}
