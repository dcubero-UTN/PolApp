<?php

namespace App\Actions;

use App\Models\ProductReturn;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProcessProductReturn
{
    public function execute(array $data): ProductReturn
    {
        return DB::transaction(function () use ($data) {
            // Validate sale and product exist
            $sale = Sale::findOrFail($data['sale_id']);
            $product = Product::findOrFail($data['product_id']);

            // Validate product was actually sold in this sale
            $saleItem = $sale->items()->where('product_id', $product->id)->first();
            if (!$saleItem) {
                throw new \Exception("Este producto no fue vendido en esta venta.");
            }

            // Validate quantity
            if ($data['quantity'] > $saleItem->quantity) {
                throw new \Exception("No se puede devolver más cantidad de la vendida.");
            }

            // Calculate refunded amount (use unit_price from sale_item)
            $refundedAmount = $saleItem->unit_price * $data['quantity'];

            // 1. Create ProductReturn record
            $return = ProductReturn::create([
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'quantity' => $data['quantity'],
                'refunded_amount' => $refundedAmount,
                'product_condition' => $data['product_condition'] ?? 'nuevo',
                'reason' => $data['reason'] ?? null,
            ]);

            // 2. Reintegrate stock (only if in good condition)
            if ($data['product_condition'] === 'nuevo') {
                $product->increment('stock', $data['quantity']);
            }
            // If 'dañado', we don't add back to available stock (merma)

            // 3. Adjust Sale Balance
            $sale->decrement('current_balance', $refundedAmount);

            // If balance is 0 or negative, mark as returned/canceled
            if ($sale->fresh()->current_balance <= 0) {
                $sale->update(['status' => 'devuelto']);
            }

            // 4. Adjust Client Global Balance
            $client = Client::find($sale->client_id);
            $client->decrement('current_balance', $refundedAmount);

            return $return;
        });
    }
}
