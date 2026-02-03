<?php

namespace Tests\Feature;

use App\Livewire\ProductForm;
use App\Livewire\ProductIndex;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product()
    {
        Storage::fake('public');
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // GD might be missing, use create('product.jpg') which makes a dummy file
        // However, 'image' validation might fail if it checks mime type heavily.
        // Let's try create with a known image mime type or just disable image validation for the test environment?
        // Actually, just creating a file with jpg extension usually works for basic 'image' rule if mime detection isn't strict or if we mock it.
        // But to be safe and avoid GD error, we use create.
        $file = UploadedFile::fake()->create('product.jpg', 100);

        Livewire::actingAs($admin)
            ->test(ProductForm::class)
            ->set('sku', 'TEST-001')
            ->set('name', 'Olla Presión')
            ->set('cost_price', 10000)
            ->set('sale_price', 15000)
            ->set('stock', 20)
            ->set('min_stock_alert', 5)
            ->set('image', $file)
            ->call('save')
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'sku' => 'TEST-001',
            'cost_price' => 10000,
        ]);

        $product = Product::where('sku', 'TEST-001')->first();
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_seller_cannot_create_product()
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $seller = User::factory()->create();
        $seller->assignRole('vendedor');

        Livewire::actingAs($seller)
            ->test(ProductForm::class)
            ->call('save')
            ->assertForbidden();
    }

    public function test_security_cost_price_hidden_for_seller()
    {
        $product = Product::factory()->create([
            'cost_price' => 5000,
            'sale_price' => 8000
        ]);

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $seller = User::factory()->create();
        $seller->assignRole('vendedor');
        $this->actingAs($seller);

        // Test Model Array Serialization
        $array = $product->toArray();
        $this->assertArrayNotHasKey('cost_price', $array);
        $this->assertArrayHasKey('sale_price', $array);

        // Test Livewire Index access
        // Ideally we check if the view contains the cost. 
        // Admin sees cost, Seller doesn't.
        $component = Livewire::actingAs($seller)->test(ProductIndex::class);
        $component->assertSee($product->name);
        $component->assertDontSee('₡' . number_format(5000, 2)); // Should not see cost formatted
    }

    public function test_admin_can_see_cost_price_and_total_value()
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $product = Product::factory()->create([
            'cost_price' => 5000,
            'stock' => 10
        ]);

        $this->actingAs($admin);
        $array = $product->toArray();
        $this->assertArrayHasKey('cost_price', $array);

        Livewire::actingAs($admin)
            ->test(ProductIndex::class)
            ->assertSee('Valor Total Bodega')
            ->assertSee('₡' . number_format(50000, 2)); // 5000 * 10
    }
}
