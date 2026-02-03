<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SaleForm extends Component
{
    // Selection Fields
    public $selected_client_id = null;
    public $selected_product_id = null;

    // Cart
    public $cart = []; // [product_id => ['name', 'price', 'quantity', 'max_stock']]

    // Financials
    public $total_amount = 0;
    public $initial_downpayment = 0;
    public $current_balance = 0;

    // Payment Plan
    public $number_of_installments = 2;
    public $quota_period = 'semanal';
    public $suggested_quota = 0;
    public $showConfirmModal = false;
    public $showSuccessModal = false;
    public $last_sale_id = null;
    public $whatsapp_message = '';
    public $client_phone = '';

    // Quick Client Creation
    public $showClientModal = false;
    public $newClient = [
        'name' => '',
        'phone_primary' => '',
        'address_details' => '',
        'collection_day' => 'Lunes',
        'collection_frequency' => 'Semanal',
        'hora_cobro' => null,
    ];

    public function confirmSave()
    {
        // Forzar sincronización final antes de mostrar el modal
        $this->recalculatePayments();

        $this->validate([
            'selected_client_id' => 'required|exists:clients,id',
            'cart' => 'required|array|min:1',
            'initial_downpayment' => 'required|numeric|min:0|lte:total_amount',
            'suggested_quota' => 'required|numeric|min:0',
            'number_of_installments' => 'required|integer|min:1',
        ]);

        $this->showConfirmModal = true;
    }

    public function cancelConfirm()
    {
        $this->showConfirmModal = false;
    }

    public function openClientModal()
    {
        $this->showClientModal = true;
    }

    public function closeClientModal()
    {
        $this->showClientModal = false;
        $this->newClient = [
            'name' => '',
            'phone_primary' => '',
            'address_details' => '',
            'collection_day' => 'Lunes',
            'collection_frequency' => 'Semanal',
            'hora_cobro' => null,
        ];
    }

    public function saveNewClient()
    {
        $rules = [
            'newClient.name' => 'required|min:3',
            'newClient.phone_primary' => 'required|string|max:20|unique:clients,phone_primary',
            'newClient.address_details' => 'required',
            'newClient.collection_frequency' => 'required|in:Diario,Semanal,Quincenal,Mensual',
            'newClient.hora_cobro' => 'nullable|date_format:H:i',
        ];

        if ($this->newClient['collection_frequency'] === 'Semanal') {
            $rules['newClient.collection_day'] = 'required|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo';
        } elseif ($this->newClient['collection_frequency'] === 'Diario') {
            $rules['newClient.collection_day'] = 'nullable';
        } else {
            $rules['newClient.collection_day'] = 'required';
        }

        $this->validate($rules);

        $client = Client::create([
            'user_id' => Auth::id(),
            'name' => $this->newClient['name'],
            'phone_primary' => $this->newClient['phone_primary'],
            'address_details' => $this->newClient['address_details'],
            'collection_day' => $this->newClient['collection_day'],
            'collection_frequency' => $this->newClient['collection_frequency'],
            'hora_cobro' => $this->newClient['hora_cobro'],
            'current_balance' => 0,
        ]);

        $this->closeClientModal();

        // Enforce selection and trigger side effects
        // Force string cast to ensure HTML select compatibility
        $this->selected_client_id = (string) $client->id;
        $this->quota_period = strtolower((string) $client->collection_frequency);

        // Explicitly calculate to ensure visuals are correct
        $this->calculateTotals();

        // Dispatch browser event to force selection in the UI
        $this->dispatch('client-created', clientId: $client->id);

        session()->flash('message', 'Cliente creado y seleccionado correctamente.');
    }

    public function addToCart($productId)
    {
        if (!$productId)
            return;

        $product = Product::find($productId);
        if (!$product || $product->stock <= 0) {
            session()->flash('error', 'Producto no disponible o sin stock.');
            return;
        }

        // Forzar la clave como string para evitar que Livewire reordene el array como uno numérico
        $cartKey = "prod_" . $productId;

        if (collect($this->cart)->has($cartKey)) {
            if ($this->cart[$cartKey]['quantity'] < $product->stock) {
                $this->cart[$cartKey]['quantity']++;
            } else {
                session()->flash('error', 'No hay más stock disponible para este producto.');
            }
        } else {
            $this->cart[$cartKey] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->sale_price,
                'quantity' => 1,
                'max_stock' => (int) $product->stock
            ];
        }

        $this->calculateTotals();
    }

    public function removeFromCart($productId)
    {
        $cartKey = "prod_" . $productId;
        unset($this->cart[$cartKey]);
        $this->calculateTotals();
    }

    public function updateQuantity($productId, $qty)
    {
        $cartKey = "prod_" . $productId;
        if (isset($this->cart[$cartKey])) {
            $max = $this->cart[$cartKey]['max_stock'];
            $newQty = max(1, min($qty, $max));

            $this->cart[$cartKey]['quantity'] = $newQty;
            $this->calculateTotals();
        }
    }


    public function updated($propertyName)
    {
        if (in_array($propertyName, ['number_of_installments', 'quota_period', 'initial_downpayment'])) {
            $this->recalculatePayments();
        }

        if ($propertyName === 'newClient.collection_frequency') {
            $value = $this->newClient['collection_frequency'];
            if ($value === 'Diario') {
                $this->newClient['collection_day'] = null;
            } elseif ($value === 'Quincenal') {
                $this->newClient['collection_day'] = '15/30';
            } elseif ($value === 'Mensual') {
                $this->newClient['collection_day'] = '1';
            } else {
                $this->newClient['collection_day'] = 'Lunes';
            }
        }
    }

    protected function recalculatePayments()
    {
        // 1. Asegurar valores numéricos limpios (tratar vacíos como cero)
        $total = floatval($this->total_amount);
        $down = $this->initial_downpayment ? floatval($this->initial_downpayment) : 0;

        // 2. Validar que el abono no supere el total
        if ($down > $total) {
            $down = $total;
            $this->initial_downpayment = $down;
        }

        // 3. Calcular Saldo Pendiente
        $this->current_balance = $total - $down;
    }

    // Allow manual override logic if needed, but for now calculate on total changes

    public function calculateTotals()
    {
        $oldTotal = $this->total_amount;
        $this->total_amount = 0;
        foreach ($this->cart as $item) {
            $this->total_amount += $item['price'] * $item['quantity'];
        }

        // Nota: Se mantiene el número de plazos que el vendedor defina (por defecto 2)
        $this->recalculatePayments();
    }


    public function calculateQuota()
    {
        // Forzar recalculo de balance para asegurar que tenemos los datos más recientes
        $this->recalculatePayments();

        $installments = max(1, intval($this->number_of_installments));
        $balance = floatval($this->current_balance);

        if ($balance > 0) {
            $rawQuota = $balance / $installments;

            // Redondeo inteligente: si es exacto, mostrarlo. Si no, redondear a 100.
            if (fmod($rawQuota, 1) == 0) {
                $this->suggested_quota = (int) $rawQuota;
            } else {
                $this->suggested_quota = (int) (round($rawQuota / 100) * 100);
            }
        } else {
            $this->suggested_quota = 0;
        }
    }

    public function save()
    {
        $this->validate([
            'selected_client_id' => 'required|exists:clients,id',
            'cart' => 'required|array|min:1',
            'initial_downpayment' => 'required|numeric|min:0|lte:total_amount',
            'suggested_quota' => 'required|numeric|min:0',
            'number_of_installments' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use (&$sale) {
            // 1. Create Sale
            $sale = Sale::create([
                'client_id' => $this->selected_client_id,
                'user_id' => Auth::id(),
                'total_amount' => $this->total_amount,
                'initial_downpayment' => $this->initial_downpayment,
                'current_balance' => $this->current_balance,
                'status' => $this->current_balance == 0 ? 'pagado' : 'pendiente',
                'number_of_installments' => $this->number_of_installments,
                'suggested_quota' => $this->suggested_quota,
                'quota_period' => $this->quota_period,
            ]);

            // 2. Process Items and Stock
            foreach ($this->cart as $item) {
                // Lock product row for atomic stock update
                $product = Product::where('id', $item['id'])->lockForUpdate()->first();

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stock insuficiente para {$product->name}");
                }

                $product->stock -= $item['quantity'];
                $product->save();

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'], // Snapshot price
                    'unit_cost' => $product->cost_price, // Snapshot cost for profitability
                ]);
            }

            // 3. Update Client Balance
            $client = Client::find($this->selected_client_id);
            $client->current_balance += $this->current_balance;
            $client->save();

            $this->client_phone = $client->phone_primary;
        });

        $this->last_sale_id = $sale->id;
        $this->generateWhatsAppMessage($sale);
        $this->showConfirmModal = false;
        $this->showSuccessModal = true;

        session()->flash('message', 'Venta registrada correctamente.');
    }

    protected function generateWhatsAppMessage($sale)
    {
        $client = $sale->client;
        $date = $sale->created_at->format('d/m/Y H:i');

        $msg = "*🛒 NUEVA VENTA - POLAAPP*\n";
        $msg .= "--------------------------\n";
        $msg .= "*Fecha:* {$date}\n";
        $msg .= "*Cliente:* {$client->name}\n";
        $msg .= "--------------------------\n";
        $msg .= "*PRODUCTOS:*\n";

        foreach ($this->cart as $item) {
            $subtotal = number_format($item['price'] * $item['quantity'], 0);
            $msg .= "• {$item['quantity']}x {$item['name']} (₡{$subtotal})\n";
        }

        $msg .= "--------------------------\n";
        $msg .= "*TOTAL:* ₡" . number_format($sale->total_amount, 0) . "\n";
        $msg .= "*ABONO:* ₡" . number_format($sale->initial_downpayment, 0) . "\n";
        $msg .= "*SALDO:* ₡" . number_format($sale->current_balance, 0) . "\n";

        if ($sale->current_balance > 0) {
            $msg .= "--------------------------\n";
            $msg .= "*PLAN DE PAGOS:*\n";
            $msg .= "{$sale->number_of_installments} cuotas {$sale->quota_period}s de ₡" . number_format($sale->suggested_quota, 0) . "\n";
        }

        $msg .= "--------------------------\n";
        $msg .= "*¡Gracias por su compra!*";

        $this->whatsapp_message = rawurlencode($msg);
    }

    public function closeSuccessModal()
    {
        $this->showSuccessModal = false;
        return redirect()->route('clients.index');
    }

    public function updatedSelectedClientId($value)
    {
        if ($value) {
            $client = Client::find($value);
            if ($client && $client->collection_frequency) {
                $this->quota_period = strtolower($client->collection_frequency);
                $this->calculateTotals(); // Trigger recalculation
            }
        }
    }

    public function updatedSelectedProductId($value)
    {
        if ($value) {
            $this->addToCart($value);
            $this->selected_product_id = null; // Reset for next selection
        }
    }

    public function render()
    {
        $clients = Client::orderBy('name')
            ->when(!Auth::user()->hasRole('admin'), function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->get(['id', 'name']);

        $products = Product::where('stock', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'sale_price', 'stock', 'sku']);

        return view('livewire.sale-form', [
            'clients' => $clients,
            'products' => $products
        ])->layout('layouts.app');
    }
}
