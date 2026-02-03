<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Sale;

class SaleDetail extends Component
{
    public Sale $sale;

    public function mount(Sale $sale)
    {
        $this->sale = $sale->load(['client', 'items.product', 'payments.user', 'returns.product', 'collectionAttempts.user']);
    }

    public function render()
    {
        return view('livewire.sale-detail')->layout('layouts.app');
    }
}
