<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\Provider;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseForm extends Component
{
    public $provider_id;
    public $invoice_number;
    public $purchase_date;
    public $total_purchase = 0;

    // Cart
    public $cart = [];
    public $search_product = '';
    public $searchResults = [];

    // Quick Provider Modal
    public $showProviderModal = false;
    public $newProvider = [
        'name' => '',
        'contact_name' => '',
        'phone' => '',
        'address' => ''
    ];

    public function mount()
    {
        $this->purchase_date = now()->format('Y-m-d');
    }

    public function updatedSearchProduct($value)
    {
        if (strlen($value) < 2) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = Product::where('name', 'like', "%{$value}%")
            ->orWhere('sku', 'like', "%{$value}%")
            ->limit(5)
            ->get();
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        if (!$product)
            return;

        $key = 'prod_' . $product->id;

        if (isset($this->cart[$key])) {
            $this->cart[$key]['quantity']++;
        } else {
            $this->cart[$key] = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'quantity' => 1,
                'unit_cost' => $product->cost_price,
            ];
        }

        $this->search_product = '';
        $this->searchResults = [];
        $this->calculateTotal();
    }

    public function removeFromCart($key)
    {
        unset($this->cart[$key]);
        $this->calculateTotal();
    }

    public function incrementQuantity($key)
    {
        $this->cart[$key]['quantity']++;
        $this->calculateTotal();
    }

    public function decrementQuantity($key)
    {
        if ($this->cart[$key]['quantity'] > 1) {
            $this->cart[$key]['quantity']--;
            $this->calculateTotal();
        }
    }

    public function updateQuantity($key, $qty)
    {
        $qty = (int) $qty;
        if ($qty < 1)
            $qty = 1;

        $this->cart[$key]['quantity'] = $qty;
        $this->calculateTotal();
    }

    public function updateCost($key, $cost)
    {
        $this->cart[$key]['unit_cost'] = $cost;
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->total_purchase = array_reduce($this->cart, function ($carry, $item) {
            return $carry + ($item['quantity'] * $item['unit_cost']);
        }, 0);
    }

    public function openProviderModal()
    {
        $this->showProviderModal = true;
    }

    public function closeProviderModal()
    {
        $this->showProviderModal = false;
        $this->newProvider = ['name' => '', 'contact_name' => '', 'phone' => '', 'address' => ''];
    }

    public function saveProvider()
    {
        $this->validate([
            'newProvider.name' => 'required|string|max:255',
        ]);

        $provider = Provider::create($this->newProvider);
        $this->provider_id = $provider->id;
        $this->closeProviderModal();
        session()->flash('provider_message', 'Proveedor creado exitosamente.');
    }

    public function savePurchase()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'El carrito está vacío.');
            return;
        }

        $this->validate([
            'provider_id' => 'required|exists:providers,id',
            'purchase_date' => 'required|date',
            'invoice_number' => 'nullable|string',
        ]);

        DB::transaction(function () {
            $purchase = Purchase::create([
                'provider_id' => $this->provider_id,
                'invoice_number' => $this->invoice_number,
                'total_purchase' => $this->total_purchase,
                'purchase_date' => $this->purchase_date,
            ]);

            foreach ($this->cart as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                ]);

                $product = Product::find($item['id']);

                // Inventory Movement Record
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'type' => 'purchase',
                    'reference_type' => 'Purchase',
                    'reference_id' => $purchase->id,
                    'notes' => "Ingreso por compra #{$purchase->id}",
                    'user_id' => Auth::id(),
                ]);

                // Update Product
                $product->increment('stock', $item['quantity']);
                $product->update(['cost_price' => $item['unit_cost']]);
            }
        });

        session()->flash('message', 'Compra registrada exitosamente.');
        return redirect()->route('purchases.index');
    }

    public function render()
    {
        return view('livewire.purchase-form', [
            'providers' => Provider::all(),
        ])->layout('layouts.app');
    }
}
