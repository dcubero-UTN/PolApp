<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $liquors = [
            [
                'sku' => 'LIC-001',
                'name' => 'Whisky Johnnie Walker Black Label',
                'description' => 'Whisky Escocés de 12 años, 750ml.',
                'cost_price' => 18000.00,
                'sale_price' => 25000.00,
                'stock' => 10,
                'min_stock_alert' => 3,
            ],
            [
                'sku' => 'LIC-002',
                'name' => 'Ron Centenario Añejo 7 Años',
                'description' => 'Ron premium costarricense, 750ml.',
                'cost_price' => 8500.00,
                'sale_price' => 12500.00,
                'stock' => 15,
                'min_stock_alert' => 5,
            ],
            [
                'sku' => 'LIC-003',
                'name' => 'Tequila Don Julio Reposado',
                'description' => 'Tequila 100% de agave azul, 750ml.',
                'cost_price' => 22000.00,
                'sale_price' => 32000.00,
                'stock' => 8,
                'min_stock_alert' => 2,
            ],
            [
                'sku' => 'LIC-004',
                'name' => 'Ginebra Hendrick\'s',
                'description' => 'Ginebra infusionada con pepino y pétalos de rosa, 750ml.',
                'cost_price' => 19500.00,
                'sale_price' => 28000.00,
                'stock' => 6,
                'min_stock_alert' => 2,
            ],
            [
                'sku' => 'LIC-005',
                'name' => 'Vodka Grey Goose',
                'description' => 'Vodka francés de trigo, 750ml.',
                'cost_price' => 17000.00,
                'sale_price' => 24500.00,
                'stock' => 12,
                'min_stock_alert' => 4,
            ],
        ];

        foreach ($liquors as $liquor) {
            Product::create($liquor);
        }
    }
}
