<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Client;
use App\Models\User;
use App\Models\Payment;
use Carbon\Carbon;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $vendedor = User::role('vendedor')->first();
        $products = Product::all();
        $clients = Client::all();

        if ($products->isEmpty() || $clients->isEmpty()) {
            $this->command->error('Debe haber productos y clientes para sembrar ventas.');
            return;
        }

        // 1. Create some sales from the last 7 days to test "Weekly Sales"
        foreach (range(0, 6) as $daysAgo) {
            $date = Carbon::now()->subDays($daysAgo);

            // 2 sales per day
            for ($i = 0; $i < 2; $i++) {
                $client = $clients->random();
                $product = $products->random();
                $qty = rand(1, 3);
                $total = $product->sale_price * $qty;

                $sale = Sale::create([
                    'client_id' => $client->id,
                    'user_id' => $vendedor->id,
                    'total_amount' => $total,
                    'initial_downpayment' => rand(0, 1) ? 5000 : 0,
                    'current_balance' => $total, // Adjusted below
                    'status' => 'pendiente',
                    'number_of_installments' => 8,
                    'suggested_quota' => round(($total / 8) / 500) * 500,
                    'quota_period' => 'Semanal',
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $product->sale_price,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                // Update client and sale balance if there was a downpayment
                if ($sale->initial_downpayment > 0) {
                    $sale->current_balance -= $sale->initial_downpayment;
                    $sale->save();

                    $client->current_balance += $sale->current_balance;
                    $client->save();
                } else {
                    $client->current_balance += $total;
                    $client->save();
                }
            }
        }

        // 2. Create some payments TODAY to test "Collections Today"
        $todaySales = Sale::whereDate('created_at', Carbon::today())->get();
        foreach ($todaySales as $sale) {
            $amount = 2000;
            if ($sale->current_balance >= $amount) {
                Payment::create([
                    'sale_id' => $sale->id,
                    'user_id' => $vendedor->id,
                    'amount' => $amount,
                    'balance_before' => $sale->current_balance,
                    'balance_after' => $sale->current_balance - $amount,
                    'payment_method' => 'efectivo',
                    'created_at' => Carbon::now(),
                ]);

                $sale->current_balance -= $amount;
                $sale->save();

                $client = $sale->client;
                $client->current_balance -= $amount;
                $client->save();
            }
        }

        $this->command->info('✅ Ventas y abonos de prueba generados.');
    }
}
