<?php

namespace Tests\Feature;

use App\Actions\ProcessProductReturn;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReturnTest extends TestCase
{
    use RefreshDatabase;

    public function test_return_reintegrates_stock_for_good_condition()
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $seller = User::factory()->create();
        $seller->assignRole('vendedor');

        $client = Client::create(['user_id' => $seller->id, 'name' => 'Buyer', 'phone_primary' => '999', 'current_balance' => 200]);
        $product = Product::create(['sku' => 'P1', 'name' => 'Item', 'cost_price' => 50, 'sale_price' => 100, 'stock' => 5]);

        $sale = Sale::create([
            'client_id' => $client->id,
            'user_id' => $seller->id,
            'total_amount' => 200,
            'initial_downpayment' => 0,
            'current_balance' => 200,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 100,
        ]);

        $this->actingAs($seller);

        $action = new ProcessProductReturn();
        $action->execute([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'product_condition' => 'nuevo',
            'reason' => 'Cliente cambió de opinión',
        ]);

        // Verify stock increased
        $this->assertEquals(6, $product->fresh()->stock);

        // Verify balances decreased
        $this->assertEquals(100, $sale->fresh()->current_balance);
        $this->assertEquals(100, $client->fresh()->current_balance);
    }

    public function test_return_does_not_reintegrate_stock_for_damaged()
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $seller = User::factory()->create();
        $seller->assignRole('vendedor');

        $client = Client::create(['user_id' => $seller->id, 'name' => 'Buyer', 'phone_primary' => '999', 'current_balance' => 100]);
        $product = Product::create(['sku' => 'P1', 'name' => 'Item', 'cost_price' => 50, 'sale_price' => 100, 'stock' => 5]);

        $sale = Sale::create([
            'client_id' => $client->id,
            'user_id' => $seller->id,
            'total_amount' => 100,
            'initial_downpayment' => 0,
            'current_balance' => 100,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100,
        ]);

        $this->actingAs($seller);

        $action = new ProcessProductReturn();
        $action->execute([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'product_condition' => 'dañado',
        ]);

        // Verify stock NOT increased (damaged product)
        $this->assertEquals(5, $product->fresh()->stock);

        // Verify balances still decreased
        $this->assertEquals(0, $sale->fresh()->current_balance);
        $this->assertEquals('devuelto', $sale->fresh()->status);
    }

    public function test_cannot_return_more_than_sold()
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $seller = User::factory()->create();
        $seller->assignRole('vendedor');

        $client = Client::create(['user_id' => $seller->id, 'name' => 'Buyer', 'phone_primary' => '999']);
        $product = Product::create(['sku' => 'P1', 'name' => 'Item', 'cost_price' => 50, 'sale_price' => 100, 'stock' => 10]);

        $sale = Sale::create([
            'client_id' => $client->id,
            'user_id' => $seller->id,
            'total_amount' => 100,
            'initial_downpayment' => 0,
            'current_balance' => 100,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100,
        ]);

        $this->actingAs($seller);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No se puede devolver más cantidad de la vendida');

        $action = new ProcessProductReturn();
        $action->execute([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 5, // More than sold
            'product_condition' => 'nuevo',
        ]);
    }
}
